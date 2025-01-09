<template>
    <div class="wrapper">
        <Navbar />
        <DatatableTexts />
        <Footer />
    </div>
</template>


<script>
import Navbar from "../components/gerais/Navbar.vue";
import Footer from "../components/gerais/Footer.vue";
import DatatableTexts from '../components/textos/DatatableTexts.vue';

export default {
  name: 'Texts',
  components: { Navbar, Footer, DatatableTexts},
  }
</script>

<style scoped>
.wrapper{
  margin-bottom: 200px;
}
</style>