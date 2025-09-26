<template>
  <div class="home" style="padding: 40px 24px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
      <h1 style="font-size: 48px; font-weight: 700; line-height: 120%; color: #303030; margin-bottom: 24px;">
        <span v-if="isAuthenticated">Добро пожаловать, {{ user?.profile?.full_name || user?.name }}!</span>
        <span v-else>Добро пожаловать в Claims App</span>
      </h1>
      <p style="font-size: 20px; font-weight: 400; line-height: 140%; color: #767676; margin-bottom: 48px;">
        <span v-if="isAuthenticated">Рады видеть вас в нашем приложении!</span>
        <span v-else>Современное приложение для управления заявками с Vue 3 и Laravel</span>
      </p>

      <!-- API Status -->
      <div style="margin-bottom: 48px;">
        <div class="card-main" style="max-width: 400px; margin: 0 auto;">
          <h3 style="font-size: 24px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 16px;">Статус API</h3>
          <div v-if="apiStatus.loading" style="color: #0077ff;">
            Загрузка...
          </div>
          <div v-else-if="apiStatus.success" style="color: #14AE5C;">
            ✅ API подключен успешно
          </div>
          <div v-else style="color: #EC221F;">
            ❌ Ошибка подключения к API
          </div>
          <div v-if="apiData" style="margin-top: 8px; font-size: 14px; color: #767676;">
            {{ apiData.message }}
          </div>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
        <div class="card-main">
          <h3 style="font-size: 20px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 16px;">API Backend</h3>
          <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #767676;">Laravel 12 с JWT аутентификацией</p>
        </div>

        <div class="card-main">
          <h3 style="font-size: 20px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 16px;">Vue 3 Frontend</h3>
          <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #767676;">Современный фронтенд с Vue Router</p>
        </div>

        <div class="card-main">
          <h3 style="font-size: 20px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 16px;">Docker</h3>
          <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #767676;">Контейнеризация для легкого развертывания</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Home',
  data() {
    return {
      apiStatus: {
        loading: true,
        success: false
      },
      apiData: null,
      isAuthenticated: false,
      user: null
    }
  },
  async mounted() {
    await this.checkApiStatus()
    this.checkAuthStatus()
    // Слушаем изменения авторизации
    window.addEventListener('auth-updated', this.handleAuthUpdate)
  },
  beforeUnmount() {
    window.removeEventListener('auth-updated', this.handleAuthUpdate)
  },
  methods: {
    async checkApiStatus() {
      try {
        const response = await axios.get('/api/info')
        this.apiStatus.success = true
        this.apiData = response.data
      } catch (error) {
        console.error('API Error:', error)
        this.apiStatus.success = false
      } finally {
        this.apiStatus.loading = false
      }
    },
    checkAuthStatus() {
      const token = localStorage.getItem('token')
      const user = localStorage.getItem('user')
      
      if (token && user) {
        this.isAuthenticated = true
        this.user = JSON.parse(user)
      }
    },
    handleAuthUpdate(event) {
      const { user, authenticated } = event.detail
      this.isAuthenticated = authenticated
      this.user = user
    }
  }
}
</script>
