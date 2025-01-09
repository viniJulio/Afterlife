<template>
  <div class="wrapper">
    <Navbar />
    <Abas />
    <Footer />
  </div>
</template>

<script>
import axios from "axios";
import router from "@/router";
import Navbar from "../components/gerais/Navbar.vue";
import Footer from "../components/gerais/Footer.vue";
import Abas from "../components/abas/Abas.vue";

export default {
  name: "viewUsers",
  components: { Navbar, Footer, Abas },
  data() {
    return {
      showModalEdit: false,
    };
  },
  async created() {
    await this.isLoggedIn();
  },
  methods: {
    async isLoggedIn() {
      try {
        const response = await axios.get(
          "http://localhost:8181/pessoas.php?acao=isAdmin",
          {
            withCredentials: true,
          }
        );
        // Extrair a parte do JSON a partir da resposta
        const jsonString = response.data.match(/\{.*\}/)[0];
        const data = JSON.parse(jsonString);

        console.log(data);

        if (data.status == "erro") {
          router.push({ path: "/acessoAdmin" });
        }
      } catch (error) {
        console.error(error);
        router.push({ path: "/acessoAdmin" });
      }
    },
  },
};
</script>

<style scoped>
.wrapper {
  margin-bottom: 200px;
}
</style>
