<template>
  <div id="app">
    <nav class="nav-main">
      <router-link to="/" class="nav-brand">Claims App</router-link>
      <ul class="nav-menu">
        <li>
          <router-link to="/" class="nav-item">Главная</router-link>
        </li>
        <li>
          <router-link to="/about" class="nav-item">О нас</router-link>
        </li>

        <!-- Показываем управление данными если пользователь авторизован -->
        <template v-if="isAuthenticated">
          <li>
            <router-link to="/products" class="nav-item">Товары</router-link>
          </li>
          <li>
            <router-link to="/sellers" class="nav-item">Продавцы</router-link>
          </li>
          <li>
            <router-link to="/claims" class="nav-item">Претензии</router-link>
          </li>
        </template>

        <!-- Показываем кнопки авторизации если пользователь не авторизован -->
        <template v-if="!isAuthenticated">
          <li>
            <router-link to="/login" class="nav-item">Войти</router-link>
          </li>
          <li>
            <router-link to="/register" class="nav-item">Регистрация</router-link>
          </li>
        </template>
      </ul>

      <!-- Показываем меню пользователя если авторизован -->
      <div v-if="isAuthenticated" class="nav-user">
        <span class="nav-item">{{ user.profile?.full_name || user.name }}</span>
        <button @click="toggleUserMenu" class="nav-item">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>

        <!-- Выпадающее меню -->
        <div v-if="showUserMenu" class="user-menu">
          <router-link to="/profile" class="user-menu-item">Профиль</router-link>
          <a href="#" class="user-menu-item">Настройки</a>
          <hr style="margin: 8px 0; border: none; border-top: 1px solid #e5e5e5;">
          <button @click="logout" class="user-menu-item" style="color: #ec221f;">
            Выйти
          </button>
        </div>
      </div>
    </nav>

    <main style="min-height: calc(100vh - 64px); background-color: #f5f5f5;">
      <router-view />
    </main>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      isAuthenticated: false,
      user: null,
      showUserMenu: false
    }
  },
  mounted() {
    this.checkAuthStatus()
    // Слушаем изменения в localStorage для обновления состояния
    window.addEventListener('storage', this.checkAuthStatus)
    // Слушаем глобальные события авторизации
    window.addEventListener('auth-updated', this.handleAuthUpdate)
  },
        beforeUnmount() {
          window.removeEventListener('storage', this.checkAuthStatus)
          window.removeEventListener('auth-updated', this.handleAuthUpdate)
          window.removeEventListener('click', this.closeUserMenu)
        },
  methods: {
    checkAuthStatus() {
      if (typeof localStorage === 'undefined') return
      
      const token = localStorage.getItem('token')
      const user = localStorage.getItem('user')

      if (token && user) {
        this.isAuthenticated = true
        this.user = JSON.parse(user)
      }
    },
    logout() {
      if (typeof localStorage !== 'undefined') {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
      }
      this.isAuthenticated = false
      this.user = null

      // Глобальное событие для обновления состояния авторизации
      window.dispatchEvent(new CustomEvent('auth-updated', {
        detail: { user: null, authenticated: false }
      }))

      this.$router.push('/')
    },
    updateAuthStatus() {
      this.checkAuthStatus()
    },
           handleAuthUpdate(event) {
             const { user, authenticated } = event.detail
             this.isAuthenticated = authenticated
             this.user = user
           },
           toggleUserMenu() {
             this.showUserMenu = !this.showUserMenu
             if (this.showUserMenu) {
               setTimeout(() => {
                 window.addEventListener('click', this.closeUserMenu)
               }, 0)
             }
           },
           closeUserMenu(event) {
             if (!this.$el.contains(event.target)) {
               this.showUserMenu = false
               window.removeEventListener('click', this.closeUserMenu)
             }
           }
  }
}
</script>
