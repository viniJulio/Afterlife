<template>
  <div>
    <v-app-bar>
      <v-app-bar-title>
        <a class="navbar-brand" :href="url">
          <img
            :src="logoNav"
            style="object-fit: contain; height: 64px"
            class="pt-1"
            alt="Afterlife Logo"
          />
        </a>
      </v-app-bar-title>
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn icon="mdi-account" v-bind="props"> </v-btn>
        </template>

        <v-list>
          <v-list-item
            v-for="(item, index) in items"
            :key="item.title || index"
            :value="item.title"
            @click="opcaoSelecionada(index)"
          >
            <template v-slot:prepend>
              <v-icon :icon="item.icon" color="#4d6279"></v-icon>
            </template>
            <v-list-item-title> {{ item.title }}</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>
    <ModalSetting
      v-if="isTitular"
      :showModalSetting="showModalSetting"
      @close="showModalSetting = false"
    />
    <ModalEditAdmin
      v-if="this.currentPath === '/admin'"
      :showEditAdmin="showEditAdmin"
      @close="showEditAdmin = false"
    />
  </div>
</template>

<script>
import axios from "axios";
import router from "@/router";
import Swal from "sweetalert2";
import ModalSetting from "./ModalSetting.vue";
import ModalEditAdmin from "../admin/ModalEditAdmin.vue";
import logoImage from "@/assets/imgs/logo.png";

export default {
  name: "Navbar",
  components: {
    ModalSetting,
    ModalEditAdmin,
  },
  data() {
    return {
      currentPath: "",
      drawer: false,
      showModalSetting: false,
      showEditAdmin: false,
      items: [],
      url: "",
      isTitular: null,
      logoNav: "",
      baseUrl: "http://localhost:8181/",
      nomeSite: "",
      favicon: "",
    };
  },
  async created() {
    this.currentPath = window.location.pathname;
    await this.checkTitular();
    this.updateMenuItems();
  },
  async mounted() {
    await this.fetchDados();
    this.atualizarNomeSite();
    this.atualizarFaviconSite(this.favicon);
    console.log(this.favicon);
  },
  methods: {
    atualizarFaviconSite(url) {
      const link =
        document.querySelector("link[rel*='icon']") ||
        document.createElement("link");
      link.type = "image/x-icon";
      link.rel = "icon";
      link.href = url; // Caminho para o favicon na pasta public
      document.getElementsByTagName("head")[0].appendChild(link);
    },
    atualizarNomeSite() {
      document.title = this.nomeSite;
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

        this.nomeSite = dados.nomeFantasia
          ? dados.nomeFantasia
          : dados.razaoSocial;

        this.favicon = this.baseUrl + dados.caminhoLogoPequena;

        if (dados.caminhoLogo.length > 0) {
          this.logoNav = this.baseUrl + dados.caminhoLogo;
        } else {
          this.logoNav = logoImage;
        }
      } catch (error) {
        this.logoNav = logoImage;
      }
    },
    updateMenuItems() {
      this.url = this.currentPath === "/admin" ? "" : "/pastas";

      this.items =
        !this.isTitular || this.currentPath === "/admin"
          ? [
              {
                title: "Editar dados",
                icon: "mdi-cog",
              },
              {
                title: "Sair",
                icon: "mdi-logout",
              },
            ]
          : [
              {
                title: "Editar dados",
                icon: "mdi-cog",
              },
              {
                title: "Sair",
                icon: "mdi-logout",
              },
            ];
    },
    async checkTitular() {
      try {
        const response = await axios.get(
          "http://localhost:8181/pessoas.php?acao=isTitular",
          {
            withCredentials: true,
          }
        );

        // Extrair a parte do JSON a partir da resposta
        const jsonString = response.data.match(/\{.*\}/)[0];
        const data = JSON.parse(jsonString);

        if (data.status == false) {
          this.isTitular = false;
        } else if (data.status == true) {
          this.isTitular = true;
        }
      } catch (error) {
        console.error(error);
        router.push({ path: "/" });
      }
    },
    async logout() {
      try {
        await axios.get("http://localhost:8181/login.php?acao=logout", {
          withCredentials: true,
        });
      } catch (error) {
        console.error(error);
      }
    },
    opcaoSelecionada(index) {
      let opcao = this.items[index].title;
      switch (opcao) {
        case "Editar dados":
          if (this.currentPath === "/admin") {
            this.showEditAdmin = true;
          } else {
            this.showModalSetting = true;
          }
          break;
        case "Sair":
          Swal.fire({
            title: "Deseja sair da sua conta?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "rgba(145, 193, 65, 0.6)",
            cancelButtonColor: "rgba(255, 0, 0, 0.6)",
            confirmButtonText: "SIM",
            reverseButtons: true,
            cancelButtonText: "NÃO",
          }).then(async (result) => {
            if (result.isConfirmed) {
              await this.logout();
              Swal.fire({
                title: "Você saiu da sua conta",
                text: "Retornando à página inicial",
                icon: "success",
                timer: 2500,
                timerProgressBar: true,
                showConfirmButton: false,
              }).then(() => {
                router.push({ path: "/" });
              });
            }
          });
          break;
      }
    },
  },
};
</script>

<style scoped>
.navbar-brand img {
  max-width: 200px;
}

:deep(.v-list-item__spacer) {
  width: 16px !important;
}
</style>
