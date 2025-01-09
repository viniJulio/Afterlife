<template>
  <div class="wrapper">
    <FormRedefinirSenha />
  </div>
</template>
<script>
import axios from "axios";
import FormRedefinirSenha from "@/components/home/FormRedefinirSenha.vue";

export default {
  name: "viewRedefinirSenha",
  components: { FormRedefinirSenha },
  data() {
    return {
      baseUrl: "http://localhost:8181",
      nomeSite: "",
      favicon: "",
    };
  },
  async mounted() {
    await this.fetchDados();
    this.atualizarNomeSite();
    this.atualizarFaviconSite(this.favicon);
  },
  methods: {
    atualizarNomeSite() {
      document.title = this.nomeSite;
    },
    atualizarFaviconSite(url) {
      const link =
        document.querySelector("link[rel*='icon']") ||
        document.createElement("link");
      link.type = "image/x-icon";
      link.rel = "icon";
      link.href = url; // Caminho para o favicon na pasta public
      document.getElementsByTagName("head")[0].appendChild(link);
    },
    async fetchDados() {
      try {
        const response = await axios.get("http://localhost:8181/empresas.php");

        if (!response.data[0]) {
          this.nomeSite = "AfterLife";
          return;
        }

        const dados = response.data[0];

        this.nomeSite = dados.nomeFantasia
          ? dados.nomeFantasia
          : dados.razaoSocial;

        this.favicon = this.baseUrl + dados.caminhoLogoPequena;
      } catch (error) {
        console.log(error);
      }
    },
  },
};
</script>
<style scoped>
.wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  justify-content: center;
}
</style>
