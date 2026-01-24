<script setup>
import { ref, watchEffect, onMounted, onUnmounted } from 'vue';
const isDarkMode = ref(localStorage.getItem('darkMode') === 'true');

const toggleDarkMode = () => {
	isDarkMode.value = !isDarkMode.value;
	localStorage.setItem('darkMode', isDarkMode.value);
	document.documentElement.classList.toggle('dark', isDarkMode.value);
};
// Terapkan dark mode jika sudah diaktifkan sebelumnya
watchEffect(() => {
	if(isDarkMode.value) {
		document.documentElement.classList.add('dark');
	} else {
		document.documentElement.classList.remove('dark');
	}
});

</script>
<template>
	<div class="bg-slate-100 dark:bg-gray-900 lg:bg-gray-50/10 dark:lg:bg-gray-900/90">
		<!-- <div class="fixed flex gap-4 top-6 right-6">
			<button @click="toggleDarkMode" class="p-2 rounded-full bg-gray-900 dark:bg-gray-100 dark:text-gray-900 text-gray-100 flex items-center gap-1">
				<Icon :icon="isDarkMode ? 'solar:cloudy-moon-broken' : 'solar:sun-2-broken'" class="w-6 h-6" />
			</button>
		</div> -->
		<slot />
	</div>
</template>
