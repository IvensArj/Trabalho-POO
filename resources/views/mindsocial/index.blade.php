<x-app-layout>
    <div
        x-data="{
            photoPreview: null,
            compressing: false,
            selectedPerson: null,
            panelOpen: false,
            async handlePhotoChange(event) {
                const file = event.target.files[0];
                if (!file) {
                    this.photoPreview = null;
                    return;
                }

                this.photoPreview = URL.createObjectURL(file);

                if (file.size > 3 * 1024 * 1024) {
                    this.compressing = true;
                    try {
                        const compressed = await window.compressImage(file, 3 * 1024 * 1024, 1280);
                        const dt = new DataTransfer();
                        dt.items.add(compressed);
                        event.target.files = dt.files;
                        this.photoPreview = URL.createObjectURL(compressed);
                    } catch (err) {
                        console.error('Erro ao comprimir:', err);
                    } finally {
                        this.compressing = false;
                    }
                }
            }
        }"
        @person-selected.window="
            selectedPerson = $event.detail;
            photoPreview = null;
            panelOpen = true;

            window.dispatchEvent(new CustomEvent('fisheye-focus', { detail: { id: $event.detail.id } }));
        "
        @keyup.escape.window="selectedPerson = null; panelOpen = false; window.dispatchEvent(new CustomEvent('fisheye-unfocus'))"
    >
        @include('mindsocial.partials.header', ['people' => $people, 'groups' => $groups])
        @include('mindsocial.partials.board', ['people' => $people, 'groups' => $groups])
        @include('mindsocial.partials.modals', ['groups' => $groups])
    </div>
</x-app-layout>
