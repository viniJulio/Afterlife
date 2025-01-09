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
            <v-card-subtitle>{{ developer.name }}</v-card-subtitle>
            <p>Função: {{ developer.role }}</p>
            <div class="social-links">
              <a
                v-if="developer.github"
                :href="developer.github"
                target="_blank"
                class="mx-2"
              >
                <v-icon color="black">mdi-github</v-icon>
              </a>
              <a
                v-if="developer.linkedin"
                :href="developer.linkedin"
                target="_blank"
                class="mx-2"
              >
                <v-icon color="black">mdi-linkedin</v-icon>
              </a>
              <a
                v-if="developer.facebook"
                :href="developer.facebook"
                target="_blank"
                class="mx-2"
              >
                <v-icon color="black">mdi-facebook</v-icon>
              </a>
            </div>
          </v-col>
          <v-divider
            v-if="index < developers.length - 1"
            class="my-2"
          ></v-divider>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" text @click="modalDev = false">Fechar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from "axios";

export default {
  name: "Footer",
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
          name: "Lucas Vinícius Marques",
          role: "Front-End Developer",
          github: "https://github.com/lucas-vinicius",
          linkedin: "https://linkedin.com/in/lucas-vinicius",
          facebook: "lucas.marques",
        },
        {
          name: "Vinicios Júlio",
          role: "Back-End Developer",
          github: "https://github.com/vinicios-julio",
          linkedin: "https://linkedin.com/in/vinicios-julio",
          facebook: null,
        },
        {
          name: "Ana Silva",
          role: "UI/UX Designer",
          github: null,
          linkedin: "https://linkedin.com/in/ana-silva",
          facebook: "ana.silva",
        },
        {
          name: "Carlos Oliveira",
          role: "DevOps Engineer",
          github: "https://github.com/carlos-oliveira",
          linkedin: null,
          facebook: null,
        },
        {
          name: "Mariana Costa",
          role: "Full-Stack Developer",
          github: "https://github.com/mariana-costa",
          linkedin: "https://linkedin.com/in/mariana-costa",
          facebook: "mariana.costa",
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
  position: absolute;
  background-color: #617a95;
  padding: 0px 0 20px 0;
  text-align: center;
  right: 0;
  left: 0;
  bottom: 0;
  padding-top: 20px;
  text-align: center;
}

footer a {
  color: #fff;
  text-decoration: none;
  letter-spacing: 0.5px;
}

.footer-link {
  padding: 0;
  font-size: 12px;
}

footer p {
  color: #fff;
  margin-bottom: 0;
  letter-spacing: 0.5px;
  font-size: 12px;
}

.textoFooterInicio {
  width: 284.94px;
}

@media screen and (max-width: 767px) {
  .separador {
    display: none;
  }

  .textoFooterInicio,
  .textoFooterFim {
    flex: 0 0 auto;
    width: 100%;
  }
}

@media screen and (min-width: 768px) {
  .textos {
    justify-content: space-around;
    display: flex;
  }
}
</style>
