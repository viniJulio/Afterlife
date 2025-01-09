<template>
  <div>
    <v-app-bar
      :elevation="1"
      scroll-behavior="hide"
      color="#fff"
      class="px-md-6"
    >
      <v-app-bar-title>
        <a class="navbar-brand" href="#">
          <img
            :src="logoNav"
            class="pt-1"
            style="object-fit: contain; height: 64px"
            alt="Afterlife Logo"
          />
        </a>
      </v-app-bar-title>
      <v-btn
        v-if="!isMobile"
        rounded="0"
        class="btn-registrar"
        @click="openModalRegister"
        style="margin-right: 10px"
      >
        REGISTRAR
      </v-btn>
      <v-btn
        v-if="!isMobile"
        rounded="0"
        class="btn-entrar"
        @click="openModalLogin"
      >
        ENTRAR
      </v-btn>
      <v-app-bar-nav-icon
        v-if="isMobile"
        @click.stop="drawer = !drawer"
      ></v-app-bar-nav-icon>
    </v-app-bar>
    <v-navigation-drawer
      v-if="isMobile"
      v-model="drawer"
      hide-overlay
      :location="drawerLocation"
    >
      <v-list class="d-flex flex-column">
        <v-btn
          v-if="isMobile"
          rounded="0"
          class="btn-registrar btn-drawer"
          @click="openModalRegister"
        >
          REGISTRAR
        </v-btn>
        <v-btn
          v-if="isMobile"
          rounded="0"
          class="btn-entrar btn-drawer"
          @click="openModalLogin"
        >
          ENTRAR
        </v-btn>
      </v-list>
    </v-navigation-drawer>

    <!-- Modais de Login e Registro -->
    <ModalLogin
      :showModal="showModalLogin"
      @close="closeModalLogin"
      @open-register="openModalRegister"
    />
    <ModalRegister
      :showModalRegister="showModalRegister"
      @close="closeModalRegister"
      @open-login="openModalLogin"
    />
  </div>
</template>

<script>
import ModalLogin from "./ModalLogin.vue";
import ModalRegister from "./ModalRegister.vue";
import logoImage from "@/assets/imgs/logo.png";
import axios from "axios";

export default {
  name: "NavbarHome",
  components: {
    ModalLogin,
    ModalRegister,
  },
  data() {
    return {
      showModalLogin: false,
      showModalRegister: false,
      drawer: false,
      logoNav: "",
      baseUrl: "http://localhost:8181/",
      nomeSite: "",
      favicon: "",
    };
  },
  computed: {
    isMobile() {
      return this.$vuetify.display.smAndDown;
    },
    drawerLocation() {
      return this.isMobile ? "top" : false;
    },
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
      link.href = url;
      document.getElementsByTagName("head")[0].appendChild(link);
    },
    async fetchDados() {
      try {
        const response = await axios.get("http://localhost:8181/empresas.php");
        if (!response.data[0]) {
          this.logoNav = logoImage;
          this.nomeSite = "AfterLife";
          return;
        }
        const dados = response.data[0];
        this.nomeSite = dados.nomeFantasia || dados.razaoSocial;
        this.favicon = this.baseUrl + dados.caminhoLogoPequena;
        this.logoNav =
          dados.caminhoLogo.length > 0
            ? this.baseUrl + dados.caminhoLogo
            : logoImage;
      } catch (error) {
        this.logoNav = logoImage;
      }
    },
    openModalLogin() {
      this.showModalLogin = true;
      this.showModalRegister = false;
    },
    closeModalLogin() {
      this.showModalLogin = false;
    },
    openModalRegister() {
      this.showModalRegister = true;
      this.showModalLogin = false;
    },
    closeModalRegister() {
      this.showModalRegister = false;
    },
  },
};
</script>

<style scoped>
.navbar-brand img {
  max-width: 250px;
}
.btn-registrar {
  background-color: #91c141 !important;
  color: white !important;
  padding: 5px 10px;
}
.btn-entrar {
  background-color: #617a95 !important;
  color: white !important;
}
.btn-registrar:hover {
  background-color: #6d9232 !important;
}
.btn-entrar:hover {
  background-color: #4d6279 !important;
}
.btn-drawer {
  width: 150px;
  margin: 5px auto;
}
.v-navigation-drawer__scrim {
  height: 0;
}
</style>
