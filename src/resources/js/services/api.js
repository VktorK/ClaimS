// Универсальная функция для API запросов с автоматическим обновлением токенов
async function apiRequest(url, options = {}) {
    const token = typeof localStorage !== 'undefined' ? localStorage.getItem('token') : null
    
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    }
    
    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    }
    
    try {
        const response = await fetch(url, mergedOptions)
        
        // Проверяем, если ответ не JSON (например, HTML редирект)
        const contentType = response.headers.get('content-type')
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Non-JSON response received')
        }
        
        // Если токен истек (401), пытаемся обновить его
        if (response.status === 401) {
            const refreshToken = typeof localStorage !== 'undefined' ? localStorage.getItem('refresh_token') : null
            if (refreshToken) {
                try {
                    const refreshResponse = await fetch('/api/auth/refresh', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${refreshToken}`
                        }
                    })
                    
                    if (refreshResponse.ok) {
                        const refreshData = await refreshResponse.json()
                        if (typeof localStorage !== 'undefined') {
                            localStorage.setItem('token', refreshData.data.token)
                        }
                        
                        // Повторяем запрос с новым токеном
                        mergedOptions.headers.Authorization = `Bearer ${refreshData.data.token}`
                        const retryResponse = await fetch(url, mergedOptions)
                        return retryResponse.json()
                    }
                } catch (refreshError) {
                    console.error('Token refresh failed:', refreshError)
                }
            }
            
            // Если обновление не удалось, перенаправляем на страницу входа
            if (typeof localStorage !== 'undefined') {
                localStorage.removeItem('token')
                localStorage.removeItem('refresh_token')
                localStorage.removeItem('user')
            }
            if (typeof window !== 'undefined') {
                window.dispatchEvent(new CustomEvent('auth-updated', {
                    detail: { user: null, authenticated: false }
                }))
                window.location.href = '/login'
            }
            throw new Error('Authentication failed')
        }
        
        return response.json()
    } catch (error) {
        console.error('API request failed:', error)
        throw error
    }
}

// API сервис для работы с претензиями
export class ClaimAPI {
    static baseURL = '/api/claims'

    // Получить список претензий
    static async getClaims(params = {}) {
        const queryParams = new URLSearchParams(params).toString()
        const url = queryParams ? `${this.baseURL}?${queryParams}` : this.baseURL
        return apiRequest(url)
    }

    // Получить конкретную претензию
    static async getClaim(id) {
        return apiRequest(`${this.baseURL}/${id}`)
    }

    // Создать претензию
    static async createClaim(data) {
        return apiRequest(this.baseURL, {
            method: 'POST',
            body: JSON.stringify(data)
        })
    }

    // Обновить претензию
    static async updateClaim(id, data) {
        return apiRequest(`${this.baseURL}/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        })
    }

    // Удалить претензию
    static async deleteClaim(id) {
        return apiRequest(`${this.baseURL}/${id}`, {
            method: 'DELETE'
        })
    }

    // Получить статистику претензий
    static async getStatistics() {
        return apiRequest(`${this.baseURL}/statistics`)
    }

    // Получить претензии товара
    static async getClaimsByProduct(productId) {
        return apiRequest(`${this.baseURL}/product/${productId}`)
    }
}

// API сервис для работы с товарами
export class ProductAPI {
    static baseURL = '/api/products'

    // Получить список товаров
    static async getProducts(params = {}) {
        const queryParams = new URLSearchParams(params).toString()
        const url = queryParams ? `${this.baseURL}?${queryParams}` : this.baseURL
        return apiRequest(url)
    }

    // Получить конкретный товар
    static async getProduct(id) {
        return apiRequest(`${this.baseURL}/${id}`)
    }

    // Создать товар
    static async createProduct(data) {
        return apiRequest(this.baseURL, {
            method: 'POST',
            body: JSON.stringify(data)
        })
    }

    // Обновить товар
    static async updateProduct(id, data) {
        return apiRequest(`${this.baseURL}/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        })
    }

    // Удалить товар
    static async deleteProduct(id) {
        return apiRequest(`${this.baseURL}/${id}`, {
            method: 'DELETE'
        })
    }

    // Получить статистику товаров
    static async getStatistics() {
        return apiRequest(`${this.baseURL}/statistics`)
    }

    // Получить товары продавца
    static async getProductsBySeller(sellerId) {
        return apiRequest(`${this.baseURL}/seller/${sellerId}`)
    }
}

// API сервис для работы с продавцами
export class SellerAPI {
    static baseURL = '/api/sellers'

    // Получить список продавцов
    static async getSellers(params = {}) {
        const queryParams = new URLSearchParams(params).toString()
        const url = queryParams ? `${this.baseURL}?${queryParams}` : this.baseURL
        return apiRequest(url)
    }

    // Получить конкретного продавца
    static async getSeller(id) {
        return apiRequest(`${this.baseURL}/${id}`)
    }

    // Создать продавца
    static async createSeller(data) {
        return apiRequest(this.baseURL, {
            method: 'POST',
            body: JSON.stringify(data)
        })
    }

    // Обновить продавца
    static async updateSeller(id, data) {
        return apiRequest(`${this.baseURL}/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        })
    }

    // Удалить продавца
    static async deleteSeller(id) {
        return apiRequest(`${this.baseURL}/${id}`, {
            method: 'DELETE'
        })
    }

    // Получить статистику продавцов
    static async getStatistics() {
        return apiRequest(`${this.baseURL}/statistics`)
    }

    // Получить товары продавца
    static async getSellerProducts(id) {
        return apiRequest(`${this.baseURL}/${id}/products`)
    }
}

// API сервис для аутентификации
export class AuthAPI {
    static baseURL = '/api/auth'

    // Вход в систему
    static async login(credentials) {
        const response = await fetch(`${this.baseURL}/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(credentials)
        })
        
        const data = await response.json()
        
        if (data.success) {
            if (typeof localStorage !== 'undefined') {
                localStorage.setItem('token', data.data.token)
                localStorage.setItem('refresh_token', data.data.refresh_token || data.data.token)
                localStorage.setItem('user', JSON.stringify(data.data.user))
            }
            
            // Отправляем событие об обновлении авторизации
            if (typeof window !== 'undefined') {
                window.dispatchEvent(new CustomEvent('auth-updated', {
                    detail: { user: data.data.user, authenticated: true }
                }))
            }
        }
        
        return data
    }

    // Выход из системы
    static async logout() {
        const token = typeof localStorage !== 'undefined' ? localStorage.getItem('token') : null
        
        if (token) {
            try {
                await fetch(`${this.baseURL}/logout`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    }
                })
            } catch (error) {
                console.error('Logout request failed:', error)
            }
        }
        
        if (typeof localStorage !== 'undefined') {
            localStorage.removeItem('token')
            localStorage.removeItem('refresh_token')
            localStorage.removeItem('user')
        }
        
        if (typeof window !== 'undefined') {
            window.dispatchEvent(new CustomEvent('auth-updated', {
                detail: { user: null, authenticated: false }
            }))
        }
    }

    // Получить информацию о текущем пользователе
    static async getCurrentUser() {
        return apiRequest(`${this.baseURL}/me`)
    }

    // Обновить токен
    static async refreshToken() {
        const refreshToken = typeof localStorage !== 'undefined' ? localStorage.getItem('refresh_token') : null
        
        if (!refreshToken) {
            throw new Error('No refresh token available')
        }
        
        const response = await fetch(`${this.baseURL}/refresh`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${refreshToken}`
            }
        })
        
        const data = await response.json()
        
        if (data.success) {
            if (typeof localStorage !== 'undefined') {
                localStorage.setItem('token', data.data.token)
                localStorage.setItem('refresh_token', data.data.refresh_token || data.data.token)
            }
        }
        
        return data
    }
}

// API сервис для потребителей
export class ConsumerAPI {
    static baseURL = '/api/consumers'

    // Получить список потребителей
    static async getConsumers(search = '') {
        const url = search ? `${this.baseURL}?search=${encodeURIComponent(search)}` : this.baseURL
        return apiRequest(url)
    }

    // Создать потребителя
    static async createConsumer(data) {
        return apiRequest(this.baseURL, {
            method: 'POST',
            body: JSON.stringify(data)
        })
    }

    // Получить потребителя
    static async getConsumer(id) {
        return apiRequest(`${this.baseURL}/${id}`)
    }

    // Обновить потребителя
    static async updateConsumer(id, data) {
        return apiRequest(`${this.baseURL}/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        })
    }

    // Удалить потребителя
    static async deleteConsumer(id) {
        return apiRequest(`${this.baseURL}/${id}`, {
            method: 'DELETE'
        })
    }
}

// API сервис для шаблонов претензий
export class ClaimTemplateAPI {
    static baseURL = '/api/claim-templates'

    // Получить список шаблонов
    static async getTemplates(search = '', isActive = null) {
        const params = new URLSearchParams()
        if (search) params.append('search', search)
        if (isActive !== null) params.append('is_active', isActive)
        
        const url = params.toString() ? `${this.baseURL}?${params}` : this.baseURL
        return apiRequest(url)
    }

    // Создать шаблон
    static async createTemplate(data) {
        return apiRequest(this.baseURL, {
            method: 'POST',
            body: JSON.stringify(data)
        })
    }

    // Получить шаблон
    static async getTemplate(id) {
        return apiRequest(`${this.baseURL}/${id}`)
    }

    // Обновить шаблон
    static async updateTemplate(id, data) {
        return apiRequest(`${this.baseURL}/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        })
    }

    // Удалить шаблон
    static async deleteTemplate(id) {
        return apiRequest(`${this.baseURL}/${id}`, {
            method: 'DELETE'
        })
    }

    // Рендерить шаблон с данными
    static async renderTemplate(id, data) {
        return apiRequest(`${this.baseURL}/${id}/render`, {
            method: 'POST',
            body: JSON.stringify({ data })
        })
    }

    // Получить доступные плейсхолдеры
    static async getPlaceholders() {
        return apiRequest(`${this.baseURL}/placeholders`)
    }
}