<template>
    <div class="wrapper">
        <Navbar />
        <DatatableVideos />
        <Footer />
    </div>
</template>


<script>
import Navbar from "../components/gerais/Navbar.vue";
import Footer from "../components/gerais/Footer.vue";
import DatatableVideos from '../components/videos/DatatableVideos.vue';

export default {
  name: 'Videos',
  components: { Navbar, Footer, DatatableVideos},
  }
</script>

<style scoped>
.wrapper{
  margin-bottom: 200px;
}
</style>