<template>
    <div class="wrapper">
      <Navbar />
   
          <PasswordTable />
 
      <Footer />
    </div>
  </template>
  
  <script>
  import Navbar from '../components/gerais/Navbar.vue';
  import Footer from '../components/gerais/Footer.vue';
  import PasswordTable from '../components/senhas/PasswordTable.vue';
  
  export default {
    components: {
      Navbar,
      Footer,
      PasswordTable
    }
  };
  </script>
  
  <style scoped>
  .wrapper{
    margin-bottom: 200px;
  }
</style>
  