<template>
  <footer>
    <div class="footer d-flex flex-column">
      <div class="container">
        <!-- Linha de conteúdo do rodapé -->
        <v-row
          justify="space-between"
          align="center"
          style="max-width: 1200px; margin: 0 auto"
        >
          <!-- Coluna de Contato -->
          <v-col
            :cols="12"
            :sm="redesSociais ? 6 : 12"
            :md="redesSociais ? 4 : 6"
            class="text-center"
          >
            <p class="font-weight-bold mt-1">CONTATO</p>
            <a href="#" class="footer-link">{{ email }}</a>
          </v-col>

          <!-- Coluna de Copyright -->
          <v-col
            :cols="12"
            :sm="12"
            :md="redesSociais ? 4 : 6"
            class="text-center order-last order-md-0 mt-md-0"
          >
            <p class="copyright mt-0">
              &copy;{{ nome.toUpperCase() }} | TODOS OS DIREITOS RESERVADOS<br />
              <a href="javascript:void(0)" @click="openDesenvolvedores"
                >VER DESENVOLVEDORES</a
              >
            </p>
          </v-col>

          <!-- Coluna de Redes Sociais -->
          <v-col
            v-if="redesSociais"
            cols="12"
            sm="6"
            md="4"
            class="text-center"
          >
            <p class="font-weight-bold mt-1">REDES SOCIAIS</p>
            <a
              v-if="twitter"
              :href="'https://x.com/' + twitter"
              target="_blank"
              class="mx-2"
            >
              <font-awesome-icon :icon="['fab', 'twitter']" />
            </a>
            <a
              v-if="instagram"
              :href="'https://instagram.com/' + instagram"
              target="_blank"
              class="mx-2"
            >
              <font-awesome-icon :icon="['fab', 'instagram']" />
            </a>
            <a
              v-if="facebook"
              :href="'https://facebook.com/' + facebook"
              target="_blank"
              class="mx-2"
            >
              <font-awesome-icon :icon="['fab', 'facebook']" />
            </a>
            <a
              v-if="bluesky"
              :href="'https://bsky.app/profile/' + bluesky"
              target="_blank"
              class="mx-2"
            >
              <font-awesome-icon :icon="['fab', 'bluesky']" />
            </a>
          </v-col>
        </v-row>
      </div>
    </div>
  </footer>

  <v-dialog v-model="modalDev" max-width="600px">
    <v-card>
      <v-card-title class="headline">Créditos dos Desenvolvedores</v-card-title>
      <v-card-text>
        <v-row v-for="(developer, index) in developers" :key="index">
          <v-col cols="12">
            <p>{{ developer.name }}</p>
            <div class="social-links">
              <v-btn
                v-if="developer.github"
                :href="developer.github"
                target="_blank"
                density="comfortable"
                elevation="1"
                icon
              >
                <v-icon color="black">mdi-github</v-icon>
              </v-btn>

              <v-btn
                v-if="developer.linkedin"
                :href="developer.linkedin"
                target="_blank"
                density="comfortable"
                elevation="1"
                icon
              >
                <v-icon color="black">mdi-linkedin</v-icon>
              </v-btn>

              <v-btn
                v-if="developer.facebook"
                :href="developer.facebook"
                target="_blank"
                density="comfortable"
                elevation="1"
                icon
              >
                <v-icon color="black">mdi-facebook</v-icon>
              </v-btn>
            </div>
          </v-col>
          <v-divider
            v-if="index < developers.length - 1"
            class="my-2"
          ></v-divider>
        </v-row>
      </v-card-text>
      <v-card-actions class="d-flex justify-center">
        <v-btn text @click="modalDev = false">Fechar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from "axios";

export default {
  name: "FooterHome",
  data() {
    return {
      baseUrl: "http://localhost:8181/",
      email: "contato@afterlife.com.br",
      nome: "AFTERLIFE",
      modalDev: false,
      redesSociais: false,
      twitter: "",
      instagram: "",
      facebook: "",
      bluesky: "",

      developers: [
        {
          name: "Anna Julia Rosa",
          github: "https://github.com/annajuliarosa",
          linkedin: null,
          facebook: null,
        },
        {
          name: "Lucas Vinícius Marques",
          github: "https://github.com/lucasmarques2907",
          linkedin: null,
          facebook: null,
        },
        {
          name: "Luiza Boaventura Mendonça Fagundes",
          github: "https://github.com/luizaboaventura",
          linkedin: null,
          facebook: null,
        },
        {
          name: "Roan Sirio Goulart Da Silva",
          github: "https://github.com/roangoulart",
          linkedin: null,
          facebook: null,
        },
        {
          name: "Vinicios Julio",
          github: "https://github.com/viniJulio",
          linkedin: null,
          facebook: null,
        },
      ],
    };
  },
  async mounted() {
    this.fetchDados();
  },
  methods: {
    openDesenvolvedores() {
      this.modalDev = true;
    },
    closeDesenvolvedores() {
      this.modalDev = false;
    },
    async fetchDados() {
      try {
        const response = await axios.get("http://localhost:8181/empresas.php");

        if (!response.data[0]) {
          return;
        }

        const dados = response.data[0];

        this.email = dados.email;
        this.nome = dados.nomeFantasia ? dados.nomeFantasia : dados.razaoSocial;

        if (typeof dados.redesSociais === "string") {
          const parsedData = JSON.parse(dados.redesSociais);

          this.redesSociais = parsedData.length > 0;

          parsedData.forEach((item) => {
            const platform = Object.keys(item)[0];
            const value = item[platform];

            if (platform === "Twitter") this.twitter = value;
            if (platform === "Instagram") this.instagram = value;
            if (platform === "Facebook") this.facebook = value;
            if (platform === "Bluesky") this.bluesky = value;
          });
        }
      } catch (error) {
        console.error(error);
      }
    },
  },
};
</script>

<style scoped>
footer {
  position: relative;
  background-color: #617a95;
  color: #fff;
  padding: 0px 0 20px 0;
  text-align: center;
  right: 0;
  left: 0;
  bottom: 0;
  padding-top: 20px;
}

.copyright {
  font-size: 10px;
}

footer a {
  color: #fff;
  text-decoration: none;
  letter-spacing: 0.5px;
}

footer p {
  color: #fff;
  margin-bottom: 0;
  margin-top: 20px;
  letter-spacing: 0.5px;
  font-size: 12px;
}

.footer-link {
  padding: 0;
  font-size: 12px;
}
</style>
