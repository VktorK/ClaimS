<template>
  <div style="max-width: 800px; margin: 0 auto; padding: 40px 24px;">
    <div class="card-main">
      <h1 style="font-size: 32px; font-weight: 700; line-height: 120%; color: #303030; margin-bottom: 32px;">Редактировать профиль</h1>

      <div v-if="loading" style="text-align: center; padding: 64px 0;">
        <div style="display: inline-block; width: 32px; height: 32px; border: 2px solid transparent; border-top: 2px solid #0077ff; border-radius: 50%; animation: spin 1s linear infinite;"></div>
        <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #767676; margin-top: 16px;">Загрузка данных профиля...</p>
      </div>

      <form v-else @submit.prevent="saveProfile" class="form-main">
        <!-- Сообщения об успехе/ошибках -->
        <div v-if="successMessage" style="background-color: #eaf8f0; border: 1px solid #14AE5C; color: #14AE5C; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;" role="alert">
          {{ successMessage }}
        </div>
        <div v-if="errors.general" style="background-color: #FDE8E7; border: 1px solid #EC221F; color: #EC221F; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;" role="alert">
          {{ errors.general[0] }}
        </div>

        <!-- Личная информация -->
        <div style="padding-bottom: 32px; border-bottom: 1px solid #e5e5e5;">
          <h2 style="font-size: 20px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 24px;">Личная информация</h2>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
            <div class="form-group">
              <label for="first_name" class="form-label">Имя *</label>
              <input
                id="first_name"
                v-model="form.first_name"
                type="text"
                required
                class="input-main"
                :class="{ 'border-red-500': errors.first_name }"
              />
              <p v-if="errors.first_name" class="form-error">{{ errors.first_name[0] }}</p>
            </div>

            <div class="form-group">
              <label for="last_name" class="form-label">Фамилия</label>
              <input
                id="last_name"
                v-model="form.last_name"
                type="text"
                class="input-main"
                :class="{ 'border-red-500': errors.last_name }"
              />
              <p v-if="errors.last_name" class="form-error">{{ errors.last_name[0] }}</p>
            </div>

            <div class="form-group">
              <label for="middle_name" class="form-label">Отчество</label>
              <input
                id="middle_name"
                v-model="form.middle_name"
                type="text"
                class="input-main"
                :class="{ 'border-red-500': errors.middle_name }"
              />
              <p v-if="errors.middle_name" class="form-error">{{ errors.middle_name[0] }}</p>
            </div>

            <div class="form-group">
              <label for="birth_date" class="form-label">Дата рождения</label>
              <input
                id="birth_date"
                v-model="form.birth_date"
                type="date"
                class="input-main"
                :class="{ 'border-red-500': errors.birth_date }"
              />
              <p v-if="errors.birth_date" class="form-error">{{ errors.birth_date[0] }}</p>
            </div>

            <div class="form-group">
              <label for="gender" class="form-label">Пол</label>
              <select
                id="gender"
                v-model="form.gender"
                class="input-main"
                :class="{ 'border-red-500': errors.gender }"
              >
                <option value="">Не выбрано</option>
                <option value="male">Мужской</option>
                <option value="female">Женский</option>
                <option value="other">Другой</option>
              </select>
              <p v-if="errors.gender" class="form-error">{{ errors.gender[0] }}</p>
            </div>

            <div class="form-group">
              <label for="phone" class="form-label">Телефон</label>
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                placeholder="+7 (999) 123-45-67"
                class="input-main"
                :class="{ 'border-red-500': errors.phone }"
              />
              <p v-if="errors.phone" class="form-error">{{ errors.phone[0] }}</p>
            </div>
          </div>
        </div>

        <!-- Адресная информация -->
        <div style="padding-bottom: 32px; border-bottom: 1px solid #e5e5e5;">
          <h2 style="font-size: 20px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 24px;">Адресная информация</h2>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
            <div class="form-group" style="grid-column: 1 / -1;">
              <label for="country" class="form-label">Страна</label>
              <input
                id="country"
                v-model="form.country"
                type="text"
                class="input-main"
                :class="{ 'border-red-500': errors.country }"
              />
              <p v-if="errors.country" class="form-error">{{ errors.country[0] }}</p>
            </div>

            <div class="form-group">
              <label for="city" class="form-label">Город</label>
              <input
                id="city"
                v-model="form.city"
                type="text"
                class="input-main"
                :class="{ 'border-red-500': errors.city }"
              />
              <p v-if="errors.city" class="form-error">{{ errors.city[0] }}</p>
            </div>

            <div class="form-group">
              <label for="postal_code" class="form-label">Почтовый индекс</label>
              <input
                id="postal_code"
                v-model="form.postal_code"
                type="text"
                class="input-main"
                :class="{ 'border-red-500': errors.postal_code }"
              />
              <p v-if="errors.postal_code" class="form-error">{{ errors.postal_code[0] }}</p>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
              <label for="address" class="form-label">Адрес</label>
              <input
                id="address"
                v-model="form.address"
                type="text"
                class="input-main"
                :class="{ 'border-red-500': errors.address }"
              />
              <p v-if="errors.address" class="form-error">{{ errors.address[0] }}</p>
            </div>
          </div>
        </div>

        <!-- Настройки приватности -->
        <div style="padding-bottom: 32px;">
          <h2 style="font-size: 20px; font-weight: 600; line-height: 120%; color: #303030; margin-bottom: 24px;">Настройки приватности</h2>
          <div style="display: flex; flex-direction: column; gap: 16px;">
            <div style="display: flex; align-items: center;">
              <input
                id="is_public"
                v-model="form.is_public"
                type="checkbox"
                style="width: 16px; height: 16px; margin-right: 12px;"
              />
              <label for="is_public" style="font-size: 16px; font-weight: 400; line-height: 140%; color: #303030;">Сделать профиль публичным</label>
            </div>
            <div style="display: flex; align-items: center;">
              <input
                id="show_email"
                v-model="form.show_email"
                type="checkbox"
                style="width: 16px; height: 16px; margin-right: 12px;"
              />
              <label for="show_email" style="font-size: 16px; font-weight: 400; line-height: 140%; color: #303030;">Показывать email в профиле</label>
            </div>
            <div style="display: flex; align-items: center;">
              <input
                id="show_phone"
                v-model="form.show_phone"
                type="checkbox"
                style="width: 16px; height: 16px; margin-right: 12px;"
              />
              <label for="show_phone" style="font-size: 16px; font-weight: 400; line-height: 140%; color: #303030;">Показывать телефон в профиле</label>
            </div>
          </div>
        </div>

        <!-- Кнопки действий -->
        <div style="display: flex; justify-content: flex-end; gap: 16px; margin-top: 32px; flex-wrap: wrap;">
          <button
            type="button"
            @click="cancelEdit"
            class="btn-main btn-secondary"
          >
            Отмена
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="btn-main"
          >
            <span v-if="loading">Сохранение...</span>
            <span v-else>Сохранить изменения</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'EditProfile',
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        middle_name: '',
        birth_date: '',
        gender: '',
        phone: '',
        country: '',
        city: '',
        address: '',
        postal_code: '',
        is_public: true,
        show_email: false,
        show_phone: false,
      },
      loading: false,
      errors: {},
      successMessage: ''
    }
  },
  async mounted() {
    await this.loadProfile()
  },
  methods: {
    async loadProfile() {
      try {
        const token = localStorage.getItem('token')
        if (!token) {
          this.$router.push('/login')
          return
        }

        const response = await axios.get('/api/profile', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        })

        if (response.data.success) {
          const profile = response.data.data.profile
          this.form = {
            first_name: profile.first_name || '',
            last_name: profile.last_name || '',
            middle_name: profile.middle_name || '',
            birth_date: profile.birth_date || '',
            gender: profile.gender || '',
            phone: profile.phone || '',
            country: profile.country || '',
            city: profile.city || '',
            address: profile.address || '',
            postal_code: profile.postal_code || '',
            is_public: profile.is_public,
            show_email: profile.show_email,
            show_phone: profile.show_phone,
          }
        }
      } catch (error) {
        console.error('Profile loading error:', error)
        if (error.response && error.response.status === 401) {
          localStorage.removeItem('token')
          localStorage.removeItem('user')
          this.$router.push('/login')
        }
      }
    },
    async saveProfile() {
      this.loading = true
      this.errors = {}
      this.successMessage = ''

      try {
        const token = localStorage.getItem('token')
        if (!token) {
          this.$router.push('/login')
          return
        }

        const response = await axios.put('/api/profile', this.form, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        })

        if (response.data.success) {
          this.successMessage = 'Профиль успешно обновлен!'
          const userData = {
            ...JSON.parse(localStorage.getItem('user')),
            profile: response.data.data.profile
          }
          localStorage.setItem('user', JSON.stringify(userData))

          // Глобальное событие для обновления состояния
          window.dispatchEvent(new CustomEvent('auth-updated', {
            detail: { user: userData, authenticated: true }
          }))

          // Перенаправляем на профиль через 2 секунды
          setTimeout(() => {
            this.$router.push('/profile')
          }, 2000)
        }
      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors || {}
        } else if (error.response && error.response.data.message) {
          this.errors = { general: [error.response.data.message] }
        } else {
          this.errors = { general: ['Произошла ошибка при сохранении профиля'] }
        }
      } finally {
        this.loading = false
      }
    },
    cancelEdit() {
      this.$router.push('/profile')
    }
  }
}
</script>

<style>
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>