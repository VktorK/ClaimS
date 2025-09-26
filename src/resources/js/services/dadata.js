// Сервис для работы с DaData API
export class DaDataAPI {
  static baseURL = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs'
  
  // API ключ DaData (замените на ваш)
  static apiToken = '9cec7595458448a523189b3dd13659d932a906f3'
  
  // Поиск организации по ИНН
  static async findOrganizationByInn(inn) {
    try {
      const response = await fetch(`${this.baseURL}/findById/party`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Token ${this.apiToken}`
        },
        body: JSON.stringify({
          query: inn
        })
      })
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      
      const data = await response.json()
      return data
    } catch (error) {
      console.error('DaData API error:', error)
      throw error
    }
  }
  
  // Поиск организации по ОГРН
  static async findOrganizationByOgrn(ogrn) {
    try {
      const response = await fetch(`${this.baseURL}/findById/party`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Token ${this.apiToken}`
        },
        body: JSON.stringify({
          query: ogrn
        })
      })
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      
      const data = await response.json()
      return data
    } catch (error) {
      console.error('DaData API error:', error)
      throw error
    }
  }
  
  // Подсказки по организациям (автодополнение)
  static async suggestOrganizations(query) {
    try {
      const response = await fetch(`${this.baseURL}/suggest/party`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Token ${this.apiToken}`
        },
        body: JSON.stringify({
          query: query,
          count: 10
        })
      })
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      
      const data = await response.json()
      return data
    } catch (error) {
      console.error('DaData API error:', error)
      throw error
    }
  }
  
  // Обработка ответа DaData и извлечение данных организации
  static processOrganizationData(dadataResponse) {
    if (!dadataResponse.suggestions || dadataResponse.suggestions.length === 0) {
      return null
    }
    
    const org = dadataResponse.suggestions[0].data
    
    return {
      name: org.name?.full_with_opf || org.name?.short_with_opf || org.name?.full || org.name?.short,
      shortName: org.name?.short_with_opf || org.name?.short,
      fullName: org.name?.full_with_opf || org.name?.full,
      address: org.address?.unrestricted_value || org.address?.value,
      inn: org.inn,
      ogrn: org.ogrn,
      kpp: org.kpp,
      okved: org.okved,
      status: org.status,
      type: org.type,
      management: org.management
    }
  }
}
