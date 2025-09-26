<template>
  <div style="max-width: 400px; margin: 0 auto; padding: 40px 24px;">
    <div class="card-main">
      <h1 style="font-size: 32px; font-weight: 700; line-height: 120%; color: #303030; margin-bottom: 32px; text-align: center;">Вход в систему</h1>

      <form @submit.prevent="handleLogin" class="form-main">
        <!-- Сообщения об успехе/ошибках -->
        <div v-if="successMessage" style="background-color: #eaf8f0; border: 1px solid #14AE5C; color: #14AE5C; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;" role="alert">
          {{ successMessage }}
        </div>
        <div v-if="errors.length > 0" style="background-color: #FDE8E7; border: 1px solid #EC221F; color: #EC221F; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;" role="alert">
          <div style="font-weight: 600; margin-bottom: 8px;">Ошибки:</div>
          <ul style="margin: 0; padding-left: 20px;">
            <li v-for="error in errors" :key="error">{{ error }}</li>
          </ul>
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="input-main"
            placeholder="Введите ваш email"
          />
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Пароль</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="input-main"
            placeholder="Введите ваш пароль"
          />
        </div>

        <div class="form-actions">
          <button
            type="submit"
            :disabled="loading"
            class="btn-main"
          >
            <span v-if="loading">Вход...</span>
            <span v-else>Войти</span>
          </button>
        </div>
      </form>

      <div style="margin-top: 32px; text-align: center;">
        <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #767676;">
          Нет аккаунта?
          <router-link to="/register" style="color: #0077ff; text-decoration: none; font-weight: 600;">
            Зарегистрироваться
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Login',
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      loading: false,
      errors: [],
      successMessage: ''
    }
  },
  methods: {
    async handleLogin() {
      this.loading = true
      this.errors = []
      this.successMessage = ''

      try {
        const response = await axios.post('/api/auth/login', this.form)

        if (response.data.success) {
          this.successMessage = 'Успешный вход!'

          // Сохраняем токен
          localStorage.setItem('token', response.data.data.token)
          localStorage.setItem('user', JSON.stringify(response.data.data.user))

          // Уведомляем родительский компонент об изменении состояния авторизации
          this.$emit('auth-success', response.data.data.user)

          // Глобальное событие для обновления состояния авторизации
          window.dispatchEvent(new CustomEvent('auth-updated', {
            detail: { user: response.data.data.user, authenticated: true }
          }))

          // Перенаправляем в профиль
          setTimeout(() => {
            this.$router.push('/profile')
          }, 1500)
        }
      } catch (error) {
        if (error.response && error.response.data.message) {
          this.errors = [error.response.data.message]
        } else if (error.response && error.response.data.errors) {
          this.errors = Object.values(error.response.data.errors).flat()
        } else {
          this.errors = ['Произошла ошибка при входе в систему']
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>