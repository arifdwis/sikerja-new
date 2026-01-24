import '../css/app.css';
import './bootstrap';

import { createInertiaApp, Link, Head } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue, route } from '../../vendor/tightenco/ziggy';

import { DateTime } from 'luxon';
import { Icon } from '@iconify/vue';

import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

import ConfirmationService from 'primevue/confirmationservice';
import PrimeVue from 'primevue/config';
import MyPreset from './datatable';
import Tooltip from 'primevue/tooltip';
import 'primeicons/primeicons.css';

const PrimeCustom = {
    ptOptions: {
        mergeSections: false,
        mergeProps: false,
    },
    ripple: false,
    theme: {
        preset: MyPreset,
        options: {
            darkModeSelector: '.dark',
        },
    },
    buttonStyle: 'contained',
    inputStyle: 'outlined',
    autoZIndex: false,
    blockUI: false,
    locale: {
        monthNames: [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ]
    }
};

import {
    FwbAccordion,
    FwbAccordionContent,
    FwbAccordionHeader,
    FwbAccordionPanel,
    FwbAlert,
    FwbAvatar,
    FwbBadge,
    FwbBreadcrumb,
    FwbBreadcrumbItem,
    FwbButton,
    FwbButtonGroup,
    FwbCard,
    FwbCheckbox,
    FwbDropdown,
    FwbFileInput,
    FwbInput,
    FwbListGroup,
    FwbListGroupItem,
    FwbModal,
    FwbNavbar,
    FwbNavbarCollapse,
    FwbNavbarLink,
    FwbNavbarLogo,
    FwbPagination,
    FwbProgress,
    FwbSelect,
    FwbSidebar,
    FwbSidebarDropdownItem,
    FwbSidebarItem,
    FwbSidebarItemGroup,
    FwbSidebarLogo,
    FwbSpinner,
    FwbTab,
    FwbTable,
    FwbTableBody,
    FwbTableCell,
    FwbTableHead,
    FwbTableHeadCell,
    FwbTableRow,
    FwbTabs,
    FwbTextarea,
    FwbTimeline,
    FwbTimelineBody,
    FwbTimelineContent,
    FwbTimelineItem,
    FwbTimelinePoint,
    FwbTimelineTime,
    FwbTimelineTitle,
    FwbToast,
    FwbToggle,
    FwbTooltip,
} from 'flowbite-vue';

const ToastOptions = {
    position: "top-right",
    timeout: 5000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: true,
    closeButton: "button",
    icon: true,
    rtl: false
};

const appName = import.meta.env.VITE_APP_NAME || 'SiKerja';

const formatDateTime = (date) => {
    if (!date) return '-';
    return DateTime.fromISO(date)
        .setLocale('id')
        .toFormat('dd MMMM yyyy HH:mm');
};

const formatDate = (date) => {
    if (!date) return '-';
    return DateTime.fromISO(date)
        .setLocale('id')
        .toFormat('dd MMM yyyy');
};

const diffForHumans = (date) => {
    if (!date) return '-';
    const now = DateTime.now();
    const targetDate = DateTime.fromISO(date);
    return targetDate.toRelative({ base: now });
};

function headerField(str) {
    return str.replace(/_/g, ' ');
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Global properties
        app.config.globalProperties.$headerField = headerField;
        app.config.globalProperties.$formatDate = formatDate;
        app.config.globalProperties.$formatDateTime = formatDateTime;
        app.config.globalProperties.$diffForHumans = diffForHumans;
        app.config.globalProperties.$route = route;

        // Global components
        app.component('Link', Link);
        app.component('Head', Head);
        app.component('Icon', Icon);

        // Flowbite components
        app.component('FwbAccordion', FwbAccordion);
        app.component('FwbAccordionContent', FwbAccordionContent);
        app.component('FwbAccordionHeader', FwbAccordionHeader);
        app.component('FwbAccordionPanel', FwbAccordionPanel);
        app.component('FwbAlert', FwbAlert);
        app.component('FwbAvatar', FwbAvatar);
        app.component('FwbBadge', FwbBadge);
        app.component('FwbBreadcrumb', FwbBreadcrumb);
        app.component('FwbBreadcrumbItem', FwbBreadcrumbItem);
        app.component('FwbButton', FwbButton);
        app.component('FwbButtonGroup', FwbButtonGroup);
        app.component('FwbCard', FwbCard);
        app.component('FwbCheckbox', FwbCheckbox);
        app.component('FwbDropdown', FwbDropdown);
        app.component('FwbFileInput', FwbFileInput);
        app.component('FwbInput', FwbInput);
        app.component('FwbListGroup', FwbListGroup);
        app.component('FwbListGroupItem', FwbListGroupItem);
        app.component('FwbModal', FwbModal);
        app.component('FwbNavbar', FwbNavbar);
        app.component('FwbNavbarCollapse', FwbNavbarCollapse);
        app.component('FwbNavbarLink', FwbNavbarLink);
        app.component('FwbNavbarLogo', FwbNavbarLogo);
        app.component('FwbPagination', FwbPagination);
        app.component('FwbProgress', FwbProgress);
        app.component('FwbSelect', FwbSelect);
        app.component('FwbSidebar', FwbSidebar);
        app.component('FwbSidebarDropdownItem', FwbSidebarDropdownItem);
        app.component('FwbSidebarItem', FwbSidebarItem);
        app.component('FwbSidebarItemGroup', FwbSidebarItemGroup);
        app.component('FwbSidebarLogo', FwbSidebarLogo);
        app.component('FwbSpinner', FwbSpinner);
        app.component('FwbTab', FwbTab);
        app.component('FwbTable', FwbTable);
        app.component('FwbTableBody', FwbTableBody);
        app.component('FwbTableCell', FwbTableCell);
        app.component('FwbTableHead', FwbTableHead);
        app.component('FwbTableHeadCell', FwbTableHeadCell);
        app.component('FwbTableRow', FwbTableRow);
        app.component('FwbTabs', FwbTabs);
        app.component('FwbTextarea', FwbTextarea);
        app.component('FwbTimeline', FwbTimeline);
        app.component('FwbTimelineBody', FwbTimelineBody);
        app.component('FwbTimelineContent', FwbTimelineContent);
        app.component('FwbTimelineItem', FwbTimelineItem);
        app.component('FwbTimelinePoint', FwbTimelinePoint);
        app.component('FwbTimelineTime', FwbTimelineTime);
        app.component('FwbTimelineTitle', FwbTimelineTitle);
        app.component('FwbToast', FwbToast);
        app.component('FwbToggle', FwbToggle);
        app.component('FwbTooltip', FwbTooltip);

        app.use(Toast, ToastOptions);
        app.use(plugin)
            .use(ZiggyVue)
            .use(ConfirmationService)
            .use(PrimeVue, PrimeCustom)
            .directive('tooltip', Tooltip);

        app.mount(el);
    },
    progress: {
        color: '#3B82F6',
    },
});
