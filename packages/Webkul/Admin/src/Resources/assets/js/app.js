/**
 * This will track all the images and fonts for publishing.
 */
import.meta.glob(["../images/**", "../fonts/**"]);

/**
 * Main vue bundler.
 */
import { createApp } from "vue/dist/vue.esm-bundler";

/**
 * Main root application registry.
 */
window.app = createApp({
    data() {
        return {
            // Sidebar state management
            isMenuActive: false,
            hoveringMenu: null,
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            
            // Mobile drawer state
            activeMenu: null,
            mobileDrawerOpen: false,
        };
    },

    methods: {
        onSubmit() {},
        
        /**
         * Handle mouse over sidebar menu item
         * @param {string} menuKey - Unique menu identifier
         */
        handleMouseOver(menuKey) {
            if (!this.isMenuActive) {
                this.hoveringMenu = menuKey;
            }
        },
        
        /**
         * Handle mouse leave from sidebar
         */
        handleMouseLeave() {
            if (!this.isMenuActive) {
                this.hoveringMenu = null;
            }
        },
        
        /**
         * Handle click outside menu to close
         * @param {Event} event - Click event
         */
        handleFocusOut(event) {
            const sidebar = this.$refs.sidebar;
            const submenu = this.$refs.submenu;
            
            if (sidebar && !sidebar.contains(event.target) &&
                (!submenu || !submenu.contains(event.target))) {
                this.isMenuActive = false;
                this.hoveringMenu = null;
            }
        },
        
        /**
         * Toggle sidebar collapsed state
         */
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
            
            // Reset menu states when toggling
            this.isMenuActive = false;
            this.hoveringMenu = null;
        },
        
        /**
         * Check if menu should show submenu
         * @param {string} menuKey - Menu identifier
         * @returns {boolean}
         */
        shouldShowSubmenu(menuKey) {
            return (this.isMenuActive && this.hoveringMenu === menuKey) ||
                   (!this.isMenuActive && this.hoveringMenu === menuKey);
        },
        
        /**
         * Toggle mobile menu expansion
         * @param {string} menuKey - Menu to toggle
         */
        toggleMobileMenu(menuKey) {
            this.activeMenu = this.activeMenu === menuKey ? null : menuKey;
        },
        
        /**
         * Automatically expand the menu containing the active page
         * Used in mobile drawer on mount
         */
        autoExpandActiveMenu() {
            // Find active menu item
            const activeLink = document.querySelector('a.bg-brandColor');
            if (!activeLink) return;
            
            // Find parent menu
            const parentMenu = activeLink.closest('[data-mobile-menu]');
            if (parentMenu) {
                const menuKey = parentMenu.getAttribute('data-mobile-menu');
                this.activeMenu = menuKey;
            }
        },
        
        /**
         * Get current responsive breakpoint
         * @returns {string} Current breakpoint name
         */
        getCurrentBreakpoint() {
            const width = window.innerWidth;
            
            if (width < 525) return 'xs';
            if (width < 768) return 'sm';
            if (width < 1024) return 'md';
            if (width < 1240) return 'lg';
            if (width < 1920) return 'xl';
            return '2xl';
        },
        
        /**
         * Check if current viewport is mobile (below lg breakpoint)
         * @returns {boolean}
         */
        isMobileViewport() {
            return window.innerWidth < 1024;
        },

        onInvalidSubmit({ values, errors, results }) {
            setTimeout(() => {
                const errorKeys = Object.entries(errors)
                    .map(([key, value]) => ({ key, value }))
                    .filter(error => error["value"].length);

                if (errorKeys.length > 0) {
                    const errorKey = errorKeys[0]["key"];

                    let scrollTarget = null;
                    
                    // Try to find the input element with the exact name first.
                    let firstErrorElement = document.querySelector('[name="' + errorKey + '"]');
                    
                    // If not found and the key doesn't end with [], try with the [] suffix (for array fields like categories[], channels[]).
                    if (
                        ! firstErrorElement 
                        && ! errorKey.endsWith('[]')
                    ) {
                        firstErrorElement = document.querySelector('[name="' + errorKey + '[]"]');
                    }
                    
                    // If still not found, try to find any element that starts with this name (for nested fields).
                    if (! firstErrorElement) {
                        firstErrorElement = document.querySelector('[name^="' + errorKey + '"]');
                    }

                    // If we found the input element.
                    if (firstErrorElement) {
                        // Check if this is a TinyMCE textarea (hidden by TinyMCE).
                        if (firstErrorElement.tagName === 'TEXTAREA' && firstErrorElement.style.display === 'none') {
                            // Find the TinyMCE editor container.
                            const editorId = firstErrorElement.id;

                            const tinyMCEContainer = document.querySelector('#' + editorId + '_parent');
                            
                            if (tinyMCEContainer) {
                                scrollTarget = tinyMCEContainer;
                            } else {
                                scrollTarget = firstErrorElement;
                            }
                        } else {
                            scrollTarget = firstErrorElement;
                        }
                    } else {
                        // If the input is not found, try to find the error message element itself.
                        // VeeValidate renders error messages with a v-error-message component having a name attribute.
                        const errorMessageElement = document.querySelector('[name="' + errorKey + '"] p, [name="' + errorKey + '[]"] p');
                        
                        if (errorMessageElement) {
                            // Scroll to the parent container of the error message.
                            scrollTarget = errorMessageElement.closest('.box-shadow') || errorMessageElement.closest('div[class*="bg-white"]') || errorMessageElement;
                        }
                    }

                    if (scrollTarget) {
                        scrollTarget.scrollIntoView({
                            behavior: "smooth",
                            block: "center"
                        });
                        
                        // Try to focus the element: for TinyMCE, focus the editor; for regular inputs, focus the input.
                        if (firstErrorElement) {
                            if (firstErrorElement.tagName === 'TEXTAREA' && firstErrorElement.style.display === 'none') {
                                // Focus the TinyMCE editor if available.
                                const editorId = firstErrorElement.id;

                                if (window.tinymce && tinymce.get(editorId)) {
                                    tinymce.get(editorId).focus();
                                }
                            } else if (firstErrorElement.focus) {
                                firstErrorElement.focus();
                            }
                        }
                    } else {
                        // If the scroll target is not found, show a flash message with all errors.
                        const allErrors = errorKeys
                            .map(error => {
                                if (Array.isArray(error.value)) {
                                    return error.value.join(', ');
                                }

                                return error.value;
                            })
                            .filter(msg => msg).join(' ');
                        
                        this.$emitter.emit('add-flash', { 
                            type: 'error', 
                            message: allErrors 
                        });
                    }
                }
            }, 100);
        },
    },
    
    mounted() {
        // Add click outside listener
        this.clickOutsideHandler = this.handleFocusOut.bind(this);
        window.addEventListener('click', this.clickOutsideHandler);
        
        // Auto-expand active menu in mobile
        this.autoExpandActiveMenu();
    },
    
    beforeUnmount() {
        // Cleanup event listeners
        if (this.clickOutsideHandler) {
            window.removeEventListener('click', this.clickOutsideHandler);
        }
    },
});

/**
 * Global plugins registration.
 */
import Admin from "./plugins/admin";
import Axios from "./plugins/axios";
import CreateElement from "./plugins/createElement";
import Emitter from "./plugins/emitter";
import Flatpickr from "./plugins/flatpickr";
import VeeValidate from "./plugins/vee-validate";
import Draggable from "./plugins/draggable";
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';

app.component('vue-cal', VueCal);

[
    Admin,
    Axios,
    CreateElement,
    Emitter,
    Flatpickr,
    VeeValidate,
    Draggable,
].forEach((plugin) => app.use(plugin));

/**
 * Global directives.
 */
import Slugify from "./directives/slugify";
import SlugifyTarget from "./directives/slugify-target";
import Debounce from "./directives/debounce";
import Code from "./directives/code";

app.directive("slugify", Slugify);
app.directive("slugify-target", SlugifyTarget);
app.directive("debounce", Debounce);
app.directive("code", Code);

export default app;
