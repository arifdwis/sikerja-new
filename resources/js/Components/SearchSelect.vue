<template>
  <div class="relative w-full" @mousedown.stop>
    <fwb-input v-model="selectedText" readonly @click="toggleDropdown" :required="required" :validation-status="validationStatus" class="flex relative items-center bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white w-full cursor-pointer" placeholder="Please select one" />
    <div v-if="open && !disabled && !readonly" class="absolute w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white mt-1 z-10 max-h-80 overflow-auto" ref="dropdown" @mousedown.stop>
      <div class="flex items-center p-2 dark:bg-gray-800">
        <input v-model="search" @input="filterOptions" class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Search..." ref="searchInput" @keydown.esc="closeDropdown" />
        <button v-if="search" @click="clearSearch" class="ml-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
          <Icon icon="solar:close-circle-bold-duotone" class="w-8 h-8" />
        </button>
      </div>
      <ul v-show="filteredOptions.length > 0">
        <li v-for="option in filteredOptions" :key="option.value" @click="selectOption(option)" class="px-3 py-2 border-t dark:border-gray-600 cursor-pointer hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-900">
          {{ option.name }}
        </li>
      </ul>
    </div>
  </div>
</template>
<script>
import { eventBus } from '@/utils/eventBus';

export default {
  props: {
    options: {
      type: Array,
      required: true,
    },
    modelValue: {
      type: [String, Number],
      default: null,
    },
    required: {
      type: Boolean,
      default: false,
    },
    readonly: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    validationStatus: {
      type: String,
      default: false,
    },
  },
  data() {
    return {
      search: '',
      open: false,
      filteredOptions: [],
      selectedText: '',
    };
  },
  watch: {
    options: {
      handler(newOptions) {
        this.filteredOptions = newOptions;
        this.updateSelectedText();
      },
      deep: true,
      immediate: true,
    },
    modelValue: {
      handler() {
        this.updateSelectedText();
      },
      immediate: true,
    },
  },
  mounted() {
    document.addEventListener('mousedown', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('mousedown', this.handleClickOutside);
    eventBus.off('close-all', this.closeDropdown);
  },
  created() {
    eventBus.on('close-all', this.closeDropdown);
  },
  methods: {
    toggleDropdown() {
      if(!this.disabled && !this.readonly) {
        if(!this.open) {
          eventBus.emit('close-all');
          this.openDropdown();
        } else {
          this.closeDropdown();
        }
      }
    },
    openDropdown() {
      this.search = '';
      this.filteredOptions = this.options;
      this.open = true;
      this.$nextTick(() => {
        this.$refs.searchInput.focus();
      });
    },
    closeDropdown() {
      this.open = false;
      this.search = '';
    },
    filterOptions() {
      this.filteredOptions = this.options.filter(option =>
        option.name.toLowerCase().includes(this.search.toLowerCase())
      );
    },
    selectOption(option) {
      this.selectedText = option.name;
      this.closeDropdown();
      this.$emit('update:modelValue', option.value);
    },
    updateSelectedText() {
      const selectedOption = this.options.find(option => option.value === this.modelValue);
      this.selectedText = selectedOption ? selectedOption.name : '';
    },
    clearSearch() {
      this.search = '';
      this.filterOptions();
    },
    handleClickOutside(event) {
      if(this.open && this.$refs.dropdown && !this.$refs.dropdown.contains(event.target)) {
        this.closeDropdown();
      }
    },
  },
};

</script>
