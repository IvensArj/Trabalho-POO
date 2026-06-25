import './bootstrap';
import './fisheye';
import * as lucide from 'lucide';

window.createLucideIcons = () => {
    lucide.createIcons({ icons: lucide.icons });
};

document.addEventListener('DOMContentLoaded', () => {
    window.createLucideIcons();
});