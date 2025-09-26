<template>
  <div style="max-width: 400px; margin: 0 auto; padding: 40px 24px;">
    <div class="card-main">
      <h1 style="font-size: 32px; font-weight: 700; line-height: 120%; color: #303030; margin-bottom: 32px; text-align: center;">Регистрация</h1>

      <form @submit.prevent="handleRegister" class="form-main">
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
          <label for="name" class="form-label">Имя</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="input-main"
            placeholder="Введите ваше имя"
          />
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
            placeholder="Введите пароль (минимум 8 символов)"
          />
        </div>

        <div class="form-group">
          <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            class="input-main"
            placeholder="Подтвердите пароль"
          />
        </div>

        <div class="form-actions">
          <button
            type="submit"
            :disabled="loading"
            class="btn-main"
          >
            <span v-if="loading">Регистрация...</span>
            <span v-else>Зарегистрироваться</span>
          </button>
        </div>
      </form>

      <div style="margin-top: 32px; text-align: center;">
        <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #767676;">
          Уже есть аккаунт?
          <router-link to="/login" style="color: #0077ff; text-decoration: none; font-weight: 600;">
            Войти
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Register',
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
      },
      loading: false,
      errors: [],
      successMessage: ''
    }
  },
  methods: {
    async handleRegister() {
      this.loading = true
      this.errors = []
      this.successMessage = ''

      // Клиентская валидация
      if (this.form.password !== this.form.password_confirmation) {
        this.errors = ['Пароли не совпадают']
        this.loading = false
        return
      }

      if (this.form.password.length < 8) {
        this.errors = ['Пароль должен содержать минимум 8 символов']
        this.loading = false
        return
      }

      try {
        const response = await axios.post('/api/auth/register', this.form)

        if (response.data.success) {
          this.successMessage = 'Регистрация прошла успешно! Добро пожаловать!'

          // Сохраняем токен и данные пользователя
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
          this.errors = ['Произошла ошибка при регистрации']
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>