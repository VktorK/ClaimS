<template>
  <div class="products-page">
    <div class="page-header">
      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-item">
          <i class="fas fa-home"></i>
          Главная
        </router-link>
        <span class="breadcrumb-separator">/</span>
               <span class="breadcrumb-item active">Товары</span>
      </div>
      
      <div class="page-title">
        <h1>Управление товарами</h1>
        <p>Просмотр и управление всеми товарами в системе</p>
      </div>
    </div>


    <div class="content-section">
      <ProductList />
    </div>
  </div>
</template>

<script>
import ProductList from '../components/ProductList.vue'

export default {
  name: 'Products',
  components: {
    ProductList
  }
}
</script>

<style scoped>
.products-page {
  min-height: 100vh;
  background: #f5f5f5;
}

.page-header {
  background: white;
  padding: 20px 30px;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 20px;
}

.breadcrumb {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
  font-size: 14px;
}

.breadcrumb-item {
  color: #666;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 5px;
}

.breadcrumb-item:hover {
  color: #007bff;
}

.breadcrumb-item.active {
  color: #333;
  font-weight: 500;
}

.breadcrumb-separator {
  margin: 0 10px;
  color: #ccc;
}

.page-title h1 {
  margin: 0;
  color: #333;
  font-size: 28px;
  font-weight: 600;
}

.page-title p {
  margin: 5px 0 0 0;
  color: #666;
  font-size: 14px;
}

.content-section {
  padding: 0 10px;
}

@media (max-width: 768px) {
  .page-header {
    padding: 15px 20px;
  }
  
  .page-title h1 {
    font-size: 24px;
  }
  
  .content-section {
    padding: 0 5px;
  }
}
</style>
