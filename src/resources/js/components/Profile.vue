<template>
  <div style="max-width: 800px; margin: 0 auto; padding: 40px 24px;">
    <div class="card-main">
      <h1 style="font-size: 32px; font-weight: 700; line-height: 120%; color: #303030; margin-bottom: 32px;">Профиль пользователя</h1>

      <!-- Информация о пользователе -->
      <div v-if="user" style="display: flex; flex-direction: column; gap: 32px;">
        <!-- Аватар и основная информация -->
        <div style="display: flex; align-items: flex-start; gap: 24px; padding-bottom: 32px; border-bottom: 1px solid #e5e5e5;">
          <div style="flex-shrink: 0; position: relative;">
            <img
              :src="user.profile?.avatar_url || '/images/default-avatar.png'"
              :alt="user.profile?.full_name || user.name"
              style="width: 96px; height: 96px; border-radius: 50%; object-fit: cover; border: 4px solid #ffffff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"
              :style="{ opacity: uploadingAvatar ? 0.5 : 1 }"
            >
            <!-- Индикатор загрузки -->
            <div v-if="uploadingAvatar" style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background-color: rgba(0,0,0,0.5); border-radius: 50%;">
              <div style="width: 32px; height: 32px; border: 2px solid transparent; border-top: 2px solid white; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            </div>
            <!-- Кнопка загрузки аватара -->
            <div style="position: absolute; bottom: -8px; right: -8px;">
              <label for="avatar-upload" style="cursor: pointer;">
                <div style="background-color: #0077ff; color: white; border-radius: 50%; padding: 8px; transition: background-color 100ms ease; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                  <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
              </label>
              <input
                id="avatar-upload"
                type="file"
                accept="image/*"
                @change="handleAvatarUpload"
                style="display: none;"
                :disabled="uploadingAvatar"
              />
            </div>
          </div>
          <div style="flex: 1;">
            <h1 style="font-size: 24px; font-weight: 600; line-height: 120%; color: #303030;">{{ user.profile?.full_name || user.name }}</h1>
            <p v-if="user.profile?.phone" style="font-size: 16px; font-weight: 400; line-height: 140%; color: #767676; margin-top: 4px;">{{ user.profile.phone }}</p>
            <p v-if="user.profile?.city" style="font-size: 14px; font-weight: 400; line-height: 140%; color: #9a9a9a; margin-top: 4px;">{{ user.profile.city }}, {{ user.profile.country }}</p>
          </div>
        </div>

        <!-- Личная информация -->
        <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">
          <div style="display: flex; flex-direction: column; gap: 16px;">
            <h3 style="font-size: 20px; font-weight: 600; line-height: 120%; color: #303030;">Личная информация</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
              <div>
                <label style="display: block; font-size: 14px; font-weight: 600; color: #767676; margin-bottom: 4px;">Email</label>
                <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #303030;">{{ user.email }}</p>
              </div>
              <div v-if="user.profile?.phone">
                <label style="display: block; font-size: 14px; font-weight: 600; color: #767676; margin-bottom: 4px;">Телефон</label>
                <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #303030;">{{ user.profile.phone }}</p>
              </div>
              <div v-if="user.profile?.birth_date">
                <label style="display: block; font-size: 14px; font-weight: 600; color: #767676; margin-bottom: 4px;">Дата рождения</label>
                <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #303030;">{{ formatDate(user.profile.birth_date) }}</p>
              </div>
              <div v-if="user.profile?.gender">
                <label style="display: block; font-size: 14px; font-weight: 600; color: #767676; margin-bottom: 4px;">Пол</label>
                <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #303030;">{{ getGenderText(user.profile.gender) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Действия -->
        <div style="display: flex; gap: 16px; flex-wrap: wrap;">
          <router-link
            to="/profile/edit"
            class="btn-main btn-secondary"
            style="text-decoration: none;"
          >
            ✏️ Редактировать профиль
          </router-link>
          <button
            @click="logout"
            class="btn-main"
            style="background-color: #EC221F; border-color: #EC221F;"
          >
            Выйти из аккаунта
          </button>
          <router-link
            to="/"
            class="btn-main"
            style="text-decoration: none;"
          >
            На главную
          </router-link>
        </div>
      </div>

      <!-- Сообщение о загрузке -->
      <div v-else-if="loading" style="text-align: center; padding: 64px 0;">
        <div style="display: inline-block; width: 32px; height: 32px; border: 2px solid transparent; border-top: 2px solid #0077ff; border-radius: 50%; animation: spin 1s linear infinite;"></div>
        <p style="font-size: 16px; font-weight: 400; line-height: 140%; color: #767676; margin-top: 16px;">Загрузка профиля...</p>
      </div>

      <!-- Сообщение об ошибке -->
      <div v-else style="text-align: center; padding: 64px 0; color: #EC221F;">
        <p style="font-size: 16px; font-weight: 400; line-height: 140%;">Не удалось загрузить профиль. Пожалуйста, войдите снова.</p>
        <router-link to="/login" style="color: #0077ff; text-decoration: none; font-weight: 600; margin-top: 16px; display: block;">Перейти к входу</router-link>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Profile',
  data() {
    return {
      user: null,
      loading: true,
      uploadingAvatar: false
    }
  },
  async mounted() {
    await this.loadProfile()
  },
  methods: {
    async loadProfile() {
      this.loading = true
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
          this.user = {
            ...response.data.data.user,
            profile: response.data.data.profile // Ensure profile is directly accessible
          }
        }
      } catch (error) {
        console.error('Profile loading error:', error)
        if (error.response && error.response.status === 401) {
          localStorage.removeItem('token')
          localStorage.removeItem('user')
          window.dispatchEvent(new CustomEvent('auth-updated', {
            detail: { user: null, authenticated: false }
          }))
          this.$router.push('/login')
        }
      } finally {
        this.loading = false
      }
    },
    logout() {
      const token = localStorage.getItem('token')

      if (token) {
        axios.post('/api/auth/logout', {}, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        }).catch(error => {
          console.error('Logout error:', error)
        })
      }

      localStorage.removeItem('token')
      localStorage.removeItem('user')

      // Глобальное событие для обновления состояния авторизации
      window.dispatchEvent(new CustomEvent('auth-updated', {
        detail: { user: null, authenticated: false }
      }))

      this.$router.push('/')
    },
    formatDate(dateString) {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },
    getGenderText(gender) {
      const genders = {
        'male': 'Мужской',
        'female': 'Женский',
        'other': 'Другой'
      }
      return genders[gender] || gender
    },
    async handleAvatarUpload(event) {
      const file = event.target.files[0]
      if (!file) return

      // Проверяем размер файла (2MB)
      if (file.size > 2 * 1024 * 1024) {
        console.error('Размер файла не должен превышать 2MB')
        return
      }

      // Проверяем тип файла
      if (!file.type.startsWith('image/')) {
        console.error('Пожалуйста, выберите изображение')
        return
      }

      this.uploadingAvatar = true

      try {
        const token = localStorage.getItem('token')
        const formData = new FormData()
        formData.append('avatar', file)

        const response = await axios.post('/api/profile/avatar', formData, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'multipart/form-data'
          }
        })

        if (response.data.success) {
          // Обновляем аватар в профиле
          this.user.profile.avatar_url = response.data.data.avatar_url
          this.user.profile.avatar = response.data.data.profile.avatar

          // Обновляем данные пользователя в localStorage
          localStorage.setItem('user', JSON.stringify(this.user))

          // Глобальное событие для обновления состояния
          window.dispatchEvent(new CustomEvent('auth-updated', {
            detail: { user: this.user, authenticated: true }
          }))

          console.log('Аватар успешно загружен!')
        }
      } catch (error) {
        console.error('Avatar upload error:', error)
        if (error.response && error.response.data.message) {
          console.error('Ошибка при загрузке аватара: ' + error.response.data.message)
        } else {
          console.error('Произошла ошибка при загрузке аватара')
        }
      } finally {
        this.uploadingAvatar = false
        // Очищаем input
        event.target.value = ''
      }
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