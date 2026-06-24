(() => {
    const canvas = document.getElementById('fisheye-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    canvas.style.touchAction = 'none';

    const rawPeople = Array.isArray(window.people) ? window.people : [];
    const defaultAvatar = '/images/default-avatar.png';

    const CONFIG = {
        zoomMin: 0.65,
        zoomMax: 2.3,
        rotationSensitivity: 0.0045,
        inertiaDamping: 0.94,
        baseRadiusFactor: 0.36,
        avatarBaseSize: 22,
        maxVerticalRotation: Math.PI / 2.45,
        zFadeStart: 0.22,
        zFadeEnd: -0.62,

        lineBaseWidth: 0.8,
        lineWidthPerGroup: 0.95,
        lineAlphaBase: 0.13,
        lineAlphaPerGroup: 0.055,
        lineDash: [4, 6],
        lineHoverThreshold: 16,
        lineDimFactor: 1,

        sphereBorderWidth: 4.5,
        sphereBorderAlpha: 0.26,
        sphereBorderGlowBlur: 18,
        sphereBorderGlowAlpha: 0.07,

        sphereFillAlpha: 0.045,

        gridMeridians: 18,
        gridParallels: 10,
        gridSegments: 80,
        gridAlpha: 0.16,
        gridEquatorAlpha: 0.26,
        gridPrimeMeridianAlpha: 0.22,
        gridLineWidth: 1.0,

        tooltipOffsetX: 18,
        tooltipOffsetY: 18,
    };

    const state = {
        rotationX: 0,
        rotationY: 0,
        velocityX: 0,
        velocityY: 0,
        zoom: 1,
        dragging: false,
        lastX: 0,
        lastY: 0,
        mouseX: 0,
        mouseY: 0,
        clientX: 0,
        clientY: 0,
        clickStartX: 0,
        clickStartY: 0,
        clickStartTime: 0,
        nodesForHitTest: [],
        hoveredNode: null,
        hoveredEdge: null,
        hoveredEdgeKey: null,
        stickyEdgeFrames: 0,
        targetRotationX: 0,
        targetRotationY: 0,
        focusActive: false,
        targetZoom: 1,
        editingPerson: false,
        focusLerp: 0.08,
        width: 0,
        height: 0,
        dpr: 1,
        selectedPersonId: null,
    };

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function edgeKey(connection) {
        return `${connection.a}-${connection.b}`;
    }

    function normalizeGroupName(name) {
        return String(name ?? '').trim().toLowerCase();
    }

    function buildGroupMap(groups) {
        const map = new Map();
        for (const group of Array.isArray(groups) ? groups : []) {
            const groupName = group.name || group;
            const key = normalizeGroupName(groupName);
            const label = String(groupName).trim();
            if (key && label && !map.has(key)) {
                map.set(key, label);
            }
        }
        return map;
    }

    function sharedGroups(mapA, mapB) {
        const result = [];
        for (const [key, label] of mapA.entries()) {
            if (mapB.has(key)) result.push(label);
        }
        return result;
    }

    function dot(a, b) {
        return a.x * b.x + a.y * b.y + a.z * b.z;
    }

    function rotateVec(x, y, z) {
        const cosX = Math.cos(state.rotationX);
        const sinX = Math.sin(state.rotationX);

        const y1 = y * cosX - z * sinX;
        const z1 = y * sinX + z * cosX;

        y = y1;
        z = z1;

        const cosY = Math.cos(state.rotationY);
        const sinY = Math.sin(state.rotationY);

        const x1 = x * cosY + z * sinY;
        const z2 = -x * sinY + z * cosY;

        return { x: x1, y, z: z2 };
    }

    function rotatePoint(point) {
        return rotateVec(point.baseX, point.baseY, point.baseZ);
    }

    function computeOpacity(z) {
        if (z >= CONFIG.zFadeStart) return 1;
        if (z <= CONFIG.zFadeEnd) return 0;
        return (z - CONFIG.zFadeEnd) / (CONFIG.zFadeStart - CONFIG.zFadeEnd);
    }

    function slerp(a, b, t) {
        const d = clamp(dot(a, b), -1, 1);
        const omega = Math.acos(d);

        if (omega < 1e-5) {
            return {
                x: a.x + (b.x - a.x) * t,
                y: a.y + (b.y - a.y) * t,
                z: a.z + (b.z - a.z) * t,
            };
        }

        const sinOmega = Math.sin(omega);
        const s1 = Math.sin((1 - t) * omega) / sinOmega;
        const s2 = Math.sin(t * omega) / sinOmega;

        return {
            x: a.x * s1 + b.x * s2,
            y: a.y * s1 + b.y * s2,
            z: a.z * s1 + b.z * s2,
        };
    }

    function distancePointToSegment(px, py, ax, ay, bx, by) {
        const abx = bx - ax;
        const aby = by - ay;
        const apx = px - ax;
        const apy = py - ay;

        const abLenSq = abx * abx + aby * aby;
        const t = abLenSq > 0 ? clamp((apx * abx + apy * aby) / abLenSq, 0, 1) : 0;

        const cx = ax + abx * t;
        const cy = ay + aby * t;

        return Math.hypot(px - cx, py - cy);
    }

    function polylineDistance(px, py, points) {
        let best = Infinity;
        for (let i = 0; i < points.length - 1; i++) {
            const d = distancePointToSegment(
                px, py,
                points[i].x, points[i].y,
                points[i + 1].x, points[i + 1].y
            );
            if (d < best) best = d;
        }
        return best;
    }

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        state.dpr = window.devicePixelRatio || 1;
        state.width = rect.width;
        state.height = rect.height;

        canvas.width = Math.max(1, Math.floor(rect.width * state.dpr));
        canvas.height = Math.max(1, Math.floor(rect.height * state.dpr));

        ctx.setTransform(state.dpr, 0, 0, state.dpr, 0, 0);
    }

    function ensureTooltip() {
        let tooltip = document.getElementById('fisheye-tooltip');
        if (tooltip) return tooltip;

        tooltip = document.createElement('div');
        tooltip.id = 'fisheye-tooltip';
        tooltip.style.position = 'fixed';
        tooltip.style.left = '0';
        tooltip.style.top = '0';
        tooltip.style.zIndex = '9999';
        tooltip.style.pointerEvents = 'none';
        tooltip.style.opacity = '0';
        tooltip.style.transition = 'opacity .12s ease';
        tooltip.style.maxWidth = '300px';
        tooltip.style.padding = '10px 12px';
        tooltip.style.borderRadius = '12px';
        tooltip.style.background = 'rgba(15, 15, 15, .88)';
        tooltip.style.border = '1px solid rgba(255,255,255,.12)';
        tooltip.style.boxShadow = '0 10px 30px rgba(0,0,0,.35)';
        tooltip.style.color = '#fff';
        tooltip.style.fontFamily = 'system-ui, -apple-system, Segoe UI, Roboto, sans-serif';
        tooltip.style.fontSize = '13px';
        tooltip.style.lineHeight = '1.35';
        tooltip.style.backdropFilter = 'blur(8px)';

        const title = document.createElement('div');
        title.id = 'fisheye-tooltip-title';
        title.style.fontWeight = '700';
        title.style.marginBottom = '6px';

        const body = document.createElement('div');
        body.id = 'fisheye-tooltip-body';
        body.style.opacity = '0.92';

        tooltip.appendChild(title);
        tooltip.appendChild(body);
        document.body.appendChild(tooltip);

        return tooltip;
    }

    const tooltip = ensureTooltip();
    const tooltipTitle = tooltip.querySelector('#fisheye-tooltip-title');
    const tooltipBody = tooltip.querySelector('#fisheye-tooltip-body');

    function hideTooltip() {
        tooltip.style.opacity = '0';
    }

    function showTooltip(title, lines, clientX, clientY) {
        tooltipTitle.textContent = title;
        tooltipBody.innerHTML = '';

        for (const line of lines) {
            const row = document.createElement('div');
            row.textContent = line;
            tooltipBody.appendChild(row);
        }

        tooltip.style.left = `${clientX + CONFIG.tooltipOffsetX}px`;
        tooltip.style.top = `${clientY + CONFIG.tooltipOffsetY}px`;
        tooltip.style.opacity = '1';

        const rect = tooltip.getBoundingClientRect();
        const overflowX = rect.right - window.innerWidth;
        const overflowY = rect.bottom - window.innerHeight;

        if (overflowX > 0) {
            tooltip.style.left = `${clientX - rect.width - CONFIG.tooltipOffsetX}px`;
        }

        if (overflowY > 0) {
            tooltip.style.top = `${clientY - rect.height - CONFIG.tooltipOffsetY}px`;
        }
    }

    const people = rawPeople.map((person, index) => {
        const image = new Image();
        image.crossOrigin = 'anonymous';
        image.src = person.photo || defaultAvatar;

        return {
            id: person.id ?? index,
            index,
            name: person.name || 'Pessoa',
            nickname: person.nickname || '',
            photo: person.photo || null,
            notes: person.notes || '',
            birth_day: person.birth_day || '',
            birth_month: person.birth_month || '',
            birth_year: person.birth_year || '',
            groups: Array.isArray(person.groups) ? person.groups : [],
            groupMap: buildGroupMap(person.groups),
            image,
            imageLoaded: false,
            baseX: 0,
            baseY: 0,
            baseZ: 0,
        };
    });

    for (const person of people) {
        person.image.onload = () => {
            person.imageLoaded = true;
        };
        person.image.onerror = () => {
            person.imageLoaded = false;
        };
    }

    function organizePeopleSoftly(items) {
        const total = Math.max(items.length, 1);

        // distribuição base uniforme
        items.forEach((person, i) => {
            const phi = Math.acos(1 - (2 * (i + 0.5)) / total);
            const theta = Math.PI * (1 + Math.sqrt(5)) * (i + 0.5);

            person.baseX = Math.sin(phi) * Math.cos(theta);
            person.baseY = Math.cos(phi);
            person.baseZ = Math.sin(phi) * Math.sin(theta);
        });

        // pequenas correções para aproximar pessoas com ligações
        const pullStrength = 0.018;
        const repelStrength = 0.012;
        const minDist = 0.18;

        for (let iter = 0; iter < 2; iter++) {
            for (let i = 0; i < items.length; i++) {
                for (let j = i + 1; j < items.length; j++) {
                    const a = items[i];
                    const b = items[j];

                    const shared = sharedGroups(a.groupMap, b.groupMap).length;
                    if (shared === 0) continue;

                    const dx = b.baseX - a.baseX;
                    const dy = b.baseY - a.baseY;
                    const dz = b.baseZ - a.baseZ;
                    const dist = Math.hypot(dx, dy, dz) || 1;

                    // puxa um pouco se tiver grupos em comum
                    const pull = pullStrength * shared;
                    a.baseX += dx * pull;
                    a.baseY += dy * pull;
                    a.baseZ += dz * pull;

                    b.baseX -= dx * pull;
                    b.baseY -= dy * pull;
                    b.baseZ -= dz * pull;

                    // empurra caso fique muito grudado
                    if (dist < minDist) {
                        const push = (minDist - dist) * repelStrength;
                        a.baseX -= (dx / dist) * push;
                        a.baseY -= (dy / dist) * push;
                        a.baseZ -= (dz / dist) * push;

                        b.baseX += (dx / dist) * push;
                        b.baseY += (dy / dist) * push;
                        b.baseZ += (dz / dist) * push;
                    }
                }
            }

            // normaliza tudo de volta para a esfera
            for (const p of items) {
                const len = Math.hypot(p.baseX, p.baseY, p.baseZ) || 1;
                p.baseX /= len;
                p.baseY /= len;
                p.baseZ /= len;
            }
        }
    }

    organizePeopleSoftly(people);

    const connections = [];
    for (let i = 0; i < people.length; i++) {
        for (let j = i + 1; j < people.length; j++) {
            const shared = sharedGroups(people[i].groupMap, people[j].groupMap);
            if (shared.length > 0) {
                connections.push({
                    a: i,
                    b: j,
                    shared,
                    sharedCount: shared.length,
                });
            }
        }
    }

    function startDrag(clientX, clientY) {
        state.dragging = true;
        state.focusActive = false;
        state.lastX = clientX;
        state.lastY = clientY;
        state.targetZoom = state.zoom;
        state.targetRotationX = state.rotationX;
        state.targetRotationY = state.rotationY;
    }

    function stopDrag() {
        state.dragging = false;
    }

    function drag(clientX, clientY) {
        if (!state.dragging) return;

        const dx = clientX - state.lastX;
        const dy = clientY - state.lastY;

        state.rotationY += dx * CONFIG.rotationSensitivity;
        state.rotationX -= dy * CONFIG.rotationSensitivity;

        state.targetRotationX = state.rotationX;
        state.targetRotationY = state.rotationY;

        state.rotationX = clamp(
            state.rotationX,
            -CONFIG.maxVerticalRotation,
            CONFIG.maxVerticalRotation
        );

        state.velocityY = dx * 0.0005;
        state.velocityX = -dy * 0.0005;

        state.lastX = clientX;
        state.lastY = clientY;
    }

    function buildArcPoints(a, b, segments = 24) {
        const points = [];
        const dotValue = clamp(dot(a, b), -1, 1);
        const omega = Math.acos(dotValue);

        const count = clamp(Math.ceil(omega * 28), segments, 48);

        for (let i = 0; i <= count; i++) {
            const t = i / count;
            const p = slerp(a, b, t);
            points.push({ x: p.x, y: p.y, z: p.z });
        }

        return points;
    }

    function drawSphereLine(getPoint, count, centerX, centerY, radius, alphaOverride) {
        let prev = null;

        for (let i = 0; i <= count; i++) {
            const u = (i / count) * Math.PI * 2;
            const base = getPoint(u);
            const r = rotateVec(base.x, base.y, base.z);

            const front = clamp((r.z + 0.18) / 0.65, 0, 1);
            const point = {
                sx: centerX + r.x * radius,
                sy: centerY + r.y * radius,
                front,
            };

            if (prev) {
                const baseAlpha = alphaOverride ?? CONFIG.gridAlpha;

                const visibility = 0.35 + (Math.min(prev.front, point.front) * 0.65);
                const alpha = visibility * baseAlpha;

                if (alpha > 0.01) {
                    ctx.strokeStyle = `rgba(255,255,255,${alpha.toFixed(3)})`;
                    ctx.beginPath();
                    ctx.moveTo(prev.sx, prev.sy);
                    ctx.lineTo(point.sx, point.sy);
                    ctx.stroke();
                }
            }

            prev = point;
        }
    }

    function drawSphereFill(centerX, centerY, radius) {
        const grad = ctx.createRadialGradient(
            centerX - radius * 0.22, centerY - radius * 0.18, radius * 0.05,
            centerX, centerY, radius
        );
        grad.addColorStop(0, `rgba(255,255,255,${CONFIG.sphereFillAlpha})`);
        grad.addColorStop(0.6, `rgba(255,255,255,0.008)`);
        grad.addColorStop(1, `rgba(0,0,0,0)`);

        ctx.save();
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.fillStyle = grad;
        ctx.fill();
        ctx.restore();
    }

    function drawSphereBorder(centerX, centerY, radius) {
        ctx.save();
        ctx.shadowColor = `rgba(255,255,255,${CONFIG.sphereBorderGlowAlpha})`;
        ctx.shadowBlur = CONFIG.sphereBorderGlowBlur;
        ctx.strokeStyle = `rgba(255,255,255,${CONFIG.sphereBorderAlpha * 0.5})`;
        ctx.lineWidth = CONFIG.sphereBorderWidth + 3;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.stroke();
        ctx.restore();

        ctx.save();
        ctx.strokeStyle = `rgba(255,255,255,${CONFIG.sphereBorderAlpha})`;
        ctx.lineWidth = CONFIG.sphereBorderWidth;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.stroke();
        ctx.restore();
    }

    function drawGlobeGrid(centerX, centerY, radius) {
        ctx.save();
        ctx.lineWidth = CONFIG.gridLineWidth;
        ctx.lineCap = 'round';
        ctx.setLineDash([]);

        for (let m = 0; m < CONFIG.gridMeridians; m++) {
            const lambda = (Math.PI * m) / CONFIG.gridMeridians;
            const isPrimeMeridian = m === 0;

            drawSphereLine(
                (u) => ({
                    x: Math.cos(u) * Math.cos(lambda),
                    y: Math.sin(u),
                    z: Math.cos(u) * Math.sin(lambda),
                }),
                CONFIG.gridSegments,
                centerX, centerY, radius,
                isPrimeMeridian ? CONFIG.gridPrimeMeridianAlpha : CONFIG.gridAlpha
            );
        }

        for (let p = 1; p < CONFIG.gridParallels; p++) {
            const phi = -Math.PI / 2 + (Math.PI * p) / CONFIG.gridParallels;
            const isEquator = Math.abs(phi) < 0.01;
            const cosPhi = Math.cos(phi);
            const sinPhi = Math.sin(phi);

            drawSphereLine(
                (u) => ({
                    x: cosPhi * Math.cos(u),
                    y: sinPhi,
                    z: cosPhi * Math.sin(u),
                }),
                CONFIG.gridSegments,
                centerX, centerY, radius,
                isEquator ? CONFIG.gridEquatorAlpha : CONFIG.gridAlpha
            );
        }

        ctx.lineWidth = CONFIG.gridLineWidth * 1.4;
        drawSphereLine(
            (u) => ({ x: Math.cos(u), y: 0, z: Math.sin(u) }),
            CONFIG.gridSegments,
            centerX, centerY, radius,
            CONFIG.gridEquatorAlpha
        );

        ctx.restore();
    }

    function drawHoveredEdge(hoveredRender) {
        ctx.save();
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.setLineDash([]);

        ctx.shadowColor = 'rgba(255,255,255,.12)';
        ctx.shadowBlur = 12;

        for (let i = 0; i < hoveredRender.arcPoints.length - 1; i++) {
            const p1 = hoveredRender.arcPoints[i];
            const p2 = hoveredRender.arcPoints[i + 1];

            const z = (p1.z + p2.z) * 0.5;
            const visibility = Math.max(0, (z + 0.05) / 1.05);

            if (visibility <= 0.01) continue;

            ctx.strokeStyle = 'rgba(255,255,255,.10)';
            ctx.lineWidth = hoveredRender.thickness + 6;
            ctx.beginPath();
            ctx.moveTo(hoveredRender.projected[i].x, hoveredRender.projected[i].y);
            ctx.lineTo(hoveredRender.projected[i + 1].x, hoveredRender.projected[i + 1].y);
            ctx.stroke();
        }
        ctx.restore();

        ctx.save();
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.setLineDash(CONFIG.lineDash);

        for (let i = 0; i < hoveredRender.arcPoints.length - 1; i++) {
            const p1 = hoveredRender.arcPoints[i];
            const p2 = hoveredRender.arcPoints[i + 1];

            const z = (p1.z + p2.z) * 0.5;
            const visibility = Math.max(0, (z + 0.05) / 1.05);

            if (visibility <= 0.01) continue;

            ctx.strokeStyle = 'rgba(255,255,255,.92)';
            ctx.lineWidth = hoveredRender.thickness + 1.5;
            ctx.beginPath();
            ctx.moveTo(hoveredRender.projected[i].x, hoveredRender.projected[i].y);
            ctx.lineTo(hoveredRender.projected[i + 1].x, hoveredRender.projected[i + 1].y);
            ctx.stroke();
        }
        ctx.restore();
    }

    canvas.addEventListener('pointerdown', (e) => {
        canvas.setPointerCapture?.(e.pointerId);
        state.clickStartX = e.clientX;
        state.clickStartY = e.clientY;
        state.clickStartTime = Date.now();
        startDrag(e.clientX, e.clientY);
    });

    canvas.addEventListener('pointermove', (e) => {
        const rect = canvas.getBoundingClientRect();
        state.mouseX = e.clientX - rect.left;
        state.mouseY = e.clientY - rect.top;
        state.clientX = e.clientX;
        state.clientY = e.clientY;

        drag(e.clientX, e.clientY);
    });

    window.addEventListener('pointerup', (e) => {
        const moved = Math.hypot(e.clientX - state.clickStartX, e.clientY - state.clickStartY);
        const elapsed = Date.now() - state.clickStartTime;

        stopDrag();

        if (moved < 6 && elapsed < 350) {
            const clickedNode = getNodeAtPosition(e.clientX - canvas.getBoundingClientRect().left, e.clientY - canvas.getBoundingClientRect().top);

            if (clickedNode) {
                onPersonClick(clickedNode);
            }
        }
    });

    window.addEventListener('pointercancel', stopDrag);



    window.addEventListener('fisheye-focus', (e) => {

        const node = state.currentNodes?.find(
            p => p.id == e.detail.id
        );

        if (!node) return;

        state.selectedPersonId = e.detail.id;
        state.focusActive = true;

        state.velocityX = 0;
        state.velocityY = 0;

        let targetX = Math.atan2(node.baseY, node.baseZ);
        if (targetX > Math.PI / 2) {
            targetX -= Math.PI;
        } else if (targetX < -Math.PI / 2) {
            targetX += Math.PI;
        }

        const z1 = node.baseY * Math.sin(targetX) + node.baseZ * Math.cos(targetX);
        const targetY = Math.atan2(-node.baseX, z1);

        state.targetRotationX = clamp(targetX, -CONFIG.maxVerticalRotation, CONFIG.maxVerticalRotation);

        let diffY = (targetY - state.rotationY) % (2 * Math.PI);
        if (diffY > Math.PI) diffY -= 2 * Math.PI;
        if (diffY < -Math.PI) diffY += 2 * Math.PI;

        state.targetRotationY = state.rotationY + diffY;

        state.targetZoom = 1.7;
    });

    window.addEventListener('fisheye-unfocus', () => {
        state.selectedPersonId = null;
    });

    canvas.addEventListener('wheel', (e) => {
        e.preventDefault();

        state.focusActive = false;
        state.targetZoom = state.zoom;

        state.zoom += e.deltaY * -0.001;
        state.zoom = clamp(state.zoom, CONFIG.zoomMin, CONFIG.zoomMax);
    }, { passive: false });

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    function getNodeAtPosition(x, y) {
        for (let i = state.nodesForHitTest.length - 1; i >= 0; i--) {
            const node = state.nodesForHitTest[i];
            const dx = x - node.screenX;
            const dy = y - node.screenY;
            const distance = Math.hypot(dx, dy);

            if (distance <= node.size * 1.05) {
                return node;
            }
        }
        return null;
    }

    function onPersonClick(person) {

        window.dispatchEvent(
            new CustomEvent('person-selected', {
                detail: {
                    id: person.id,
                    name: person.name,
                    nickname: person.nickname,
                    groups: person.groups,
                    notes: person.notes,
                    photo: person.photo,
                    birth_day: person.birth_day,
                    birth_month: person.birth_month,
                    birth_year: person.birth_year
                }
            })
        );
    }

    function render() {
        requestAnimationFrame(render);

        state.rotationX += state.velocityX;
        state.rotationY += state.velocityY;

        if (state.focusActive) {

            state.rotationX +=
                (state.targetRotationX - state.rotationX) *
                state.focusLerp;

            state.rotationY +=
                (state.targetRotationY - state.rotationY) *
                state.focusLerp;

            state.zoom +=
                (state.targetZoom - state.zoom) *
                state.focusLerp;
        }

        state.velocityX *= CONFIG.inertiaDamping;
        state.velocityY *= CONFIG.inertiaDamping;

        ctx.clearRect(0, 0, state.width, state.height);

        const width = state.width;
        const height = state.height;
        const centerX = width / 2;
        const centerY = height / 2;
        const radius = Math.min(width, height) * CONFIG.baseRadiusFactor * state.zoom;

        state.hoveredNode = null;

        const allNodes = people.map((person) => {
            const p = rotatePoint(person);
            const opacity = computeOpacity(p.z);

            return {
                ...person,
                x: p.x,
                y: p.y,
                z: p.z,
                opacity,
                screenX: centerX + p.x * radius,
                screenY: centerY + p.y * radius,
                size: CONFIG.avatarBaseSize * (0.68 + ((p.z + 1) / 2) * 0.62),
            };
        });
        
        state.currentNodes = allNodes;

        const visibleNodes = allNodes.filter((node) => node.opacity > 0.02);
        state.nodesForHitTest = visibleNodes;
        const nodeByIndex = new Map(allNodes.map((node) => [node.index, node]));

        drawSphereFill(centerX, centerY, radius);
        drawGlobeGrid(centerX, centerY, radius);
        drawSphereBorder(centerX, centerY, radius);

        const edgeRenders = [];
        let bestEdge = null;

        for (const connection of connections) {
            const a = nodeByIndex.get(connection.a);
            const b = nodeByIndex.get(connection.b);
            if (!a || !b) continue;

            const arcPoints = buildArcPoints(a, b);

            const strength = Math.min(connection.sharedCount, 8);
                
            const avgZ = (a.z + b.z) / 2;
            const zAlpha = clamp(
                (avgZ - CONFIG.zFadeEnd) / (CONFIG.zFadeStart - CONFIG.zFadeEnd),
                0,
                1
            );

            const thickness =
                0.8 + Math.pow(strength, 1.3) * 0.9;
                
            const alpha =
            (0.04 + connection.sharedCount * 0.08) * zAlpha;
                
            const projected = arcPoints.map((p) => ({
                x: centerX + p.x * radius,
                y: centerY + p.y * radius,
            }));

            let hoverDistance = Infinity;

            for (let i = 0; i < arcPoints.length - 1; i++) {
                const p1 = arcPoints[i];
                const p2 = arcPoints[i + 1];

                const z = (p1.z + p2.z) * 0.5;
                const visibility = Math.max(0, (z + 0.05) / 1.05);

                if (visibility <= 0.01) continue;

                const ax = projected[i].x;
                const ay = projected[i].y;
                const bx = projected[i + 1].x;
                const by = projected[i + 1].y;

                const d = distancePointToSegment(
                    state.mouseX,
                    state.mouseY,
                    ax, ay, bx, by
                );

                if (d < hoverDistance) hoverDistance = d;
            }

            const render = {
                connection,
                key: edgeKey(connection),
                a,
                b,
                arcPoints,
                projected,
                thickness,
                alpha,
                distance: hoverDistance,
            };

            edgeRenders.push(render);

            if (hoverDistance <= CONFIG.lineHoverThreshold + thickness * 1.2) {
                if (!bestEdge || hoverDistance < bestEdge.distance) {
                    bestEdge = render;
                }
            }
        }

        if (bestEdge) {
            state.hoveredEdge = bestEdge.connection;
            state.hoveredEdgeKey = bestEdge.key;
            state.stickyEdgeFrames = 8;
        } else if (state.stickyEdgeFrames > 0) {
            state.stickyEdgeFrames--;
            if (state.stickyEdgeFrames === 0) {
                state.hoveredEdge = null;
                state.hoveredEdgeKey = null;
            }
        }

        const hasHover = state.hoveredEdgeKey && state.stickyEdgeFrames > 0;
        const hoveredRender = hasHover
            ? edgeRenders.find((r) => r.key === state.hoveredEdgeKey)
            : null;

        for (const render of edgeRenders) {
            const isHovered = hasHover && render.key === state.hoveredEdgeKey;
            const dim = hasHover && !isHovered ? 1 : 1;

            ctx.save();
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.setLineDash(CONFIG.lineDash);
            ctx.lineWidth = render.thickness;

            for (let i = 0; i < render.arcPoints.length - 1; i++) {
                const p1 = render.arcPoints[i];
                const p2 = render.arcPoints[i + 1];

                const z = (p1.z + p2.z) * 0.5;
                const visibility = Math.max(0, (z + 0.05) / 1.05);

                if (visibility <= 0.01) continue;

                ctx.strokeStyle = `rgba(255,255,255,${(render.alpha * visibility * dim).toFixed(3)})`;
                ctx.beginPath();
                ctx.moveTo(render.projected[i].x, render.projected[i].y);
                ctx.lineTo(render.projected[i + 1].x, render.projected[i + 1].y);
                ctx.stroke();
            }
            ctx.restore();
        }

        if (hoveredRender) {
            drawHoveredEdge(hoveredRender);
        }

        visibleNodes.sort((a, b) => a.z - b.z);

        for (const node of visibleNodes) {
            const dx = state.mouseX - node.screenX;
            const dy = state.mouseY - node.screenY;
            const distance = Math.hypot(dx, dy);
            const isSelected = state.selectedPersonId == node.id;
            const drawSize = isSelected ? node.size * 1.25 : node.size;
            const hover = distance < drawSize * 1.02;

            if (hover && !hasHover) {
                state.hoveredNode = node;
            }

            ctx.save();
            ctx.globalAlpha = node.opacity;
            ctx.beginPath();
            ctx.arc(node.screenX, node.screenY, drawSize, 0, Math.PI * 2);
            ctx.clip();

            if (node.image && node.imageLoaded) {
                ctx.drawImage(
                    node.image,
                    node.screenX - drawSize,
                    node.screenY - drawSize,
                    drawSize * 2,
                    drawSize * 2
                );
            } else {
                ctx.fillStyle = '#222';
                ctx.fillRect(
                    node.screenX - drawSize,
                    node.screenY - drawSize,
                    drawSize * 2,
                    drawSize * 2
                );
            }

            ctx.restore();

            const isEndpoint =
                hasHover &&
                hoveredRender &&
                (hoveredRender.a.index === node.index || hoveredRender.b.index === node.index);

            ctx.save();
            ctx.globalAlpha = node.opacity;
            ctx.setLineDash([]);

            if (isSelected) {
                ctx.shadowColor = 'rgba(255,255,255,.8)';
                ctx.shadowBlur = 18;
                ctx.strokeStyle = '#fff';
                ctx.lineWidth = 4;
            } else if (isEndpoint) {
                ctx.shadowColor = 'rgba(255,255,255,.5)';
                ctx.shadowBlur = 10;
                ctx.strokeStyle = 'rgba(255,255,255,.95)';
                ctx.lineWidth = 2.4;
            } else if (hover) {
                ctx.strokeStyle = '#fff';
                ctx.lineWidth = 2.2;
            } else {
                ctx.strokeStyle = 'rgba(255,255,255,.28)';
                ctx.lineWidth = 1;
            }

            ctx.beginPath();
            ctx.arc(node.screenX, node.screenY, drawSize, 0, Math.PI * 2);
            ctx.stroke();
            ctx.restore();
        }

        

        if (hasHover && hoveredRender) {
            const aName = hoveredRender.a.name || 'Pessoa A';
            const bName = hoveredRender.b.name || 'Pessoa B';

            showTooltip(
                `${aName} ↔ ${bName}`,
                [
                    `${hoveredRender.connection.sharedCount} grupo(s) em comum`,
                    ...hoveredRender.connection.shared.slice(0, 8),
                    ...(hoveredRender.connection.shared.length > 8 ? ['…'] : []),
                ],
                state.clientX,
                state.clientY
            );
        } else if (state.hoveredNode) {
            const nickname = state.hoveredNode.nickname ? `@${state.hoveredNode.nickname}` : '';
            showTooltip(
                state.hoveredNode.name,
                [
                    nickname || 'Pessoa',
                    `${state.hoveredNode.groups.length} grupo(s)`,
                ],
                state.clientX,
                state.clientY
            );
        } else {
            hideTooltip();
        }
    }

    render();
})();