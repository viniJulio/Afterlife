<template>
  <div class="wrapper">
    <Navbar />
    <DatatableAudios/>
    <Footer />
  </div>
</template>
<script>
import DatatableAudios from "../components/audios/DatatableAudios.vue";

export default {
  name: "viewAudios",
  components: { Navbar, Footer, DatatableAudios },
};

import Navbar from "../components/gerais/Navbar.vue";
import Footer from "../components/gerais/Footer.vue";
</script>
<style scoped>
.wrapper{
  margin-bottom: 200px;
}
</style>