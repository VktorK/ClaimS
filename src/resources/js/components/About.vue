<template>
  <div class="about">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">О проекте</h1>
    
    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold mb-4">Технологический стек</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="font-semibold text-lg mb-2">Backend</h3>
          <ul class="list-disc list-inside space-y-1 text-gray-600">
            <li>Laravel 12</li>
            <li>MySQL 8.0</li>
            <li>JWT Authentication</li>
            <li>Docker</li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-semibold text-lg mb-2">Frontend</h3>
          <ul class="list-disc list-inside space-y-1 text-gray-600">
            <li>Vue 3</li>
            <li>Vue Router</li>
            <li>Tailwind CSS</li>
            <li>Vite</li>
          </ul>
        </div>
      </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-md mt-6">
      <h2 class="text-xl font-semibold mb-4">Архитектура</h2>
      <p class="text-gray-600">
        Приложение построено по принципу разделения фронтенда и бэкенда. 
        Laravel API предоставляет RESTful интерфейс для управления данными, 
        а Vue.js обеспечивает современный пользовательский интерфейс.
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'About'
}
</script>
