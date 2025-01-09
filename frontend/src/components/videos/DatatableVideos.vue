<template>
  <v-container>
    <div class="container">
      <v-card>
        <v-card-title class="header">
          <div class="title">
            <v-tooltip text="Voltar" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn
                  v-bind="props"
                  icon="mdi mdi-chevron-left"
                  elevation="0"
                  size="small"
                  @click="navigateTo('/pastas')"
                ></v-btn>
                <span class="title">{{ pasta.titulo }}</span>
              </template>
            </v-tooltip>
          </div>
          <v-spacer></v-spacer>
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="Pesquisar"
            single-line
            hide-details
            @input="searchVideos"
          ></v-text-field>
        </v-card-title>
        <v-data-table
          :headers="headers"
          :items="videos"
          :search="search"
          class="elevation-1"
          items-per-page-text="itens por página"
          pageText="{0}-{1} de {2}"
          no-data-text="Sem vídeos salvos"
        >
          <template v-slot:[`item.arquivo`]="{ item }">
            <video
              :src="getImageUrl(getFirstImage(item.caminho))"
              :width="110"
              :height="70"
              cover
              class="relative"
              style="object-fit: cover"
            >
              <span class="image-count">
                {{ getImageCount(item.caminho) }}
              </span>
            </video>
          </template>
          <template v-slot:[`item.titulo`]="{ item }">
            {{ truncateTitle(item.titulo) }}
            <!-- Exibindo o título -->
          </template>
          <template v-slot:[`item.dataCriacao`]="{ item }">
            {{ formatDate(item.dataCriacao) }}

            <!-- Exibindo a data de criação -->
          </template>
          <template v-slot:[`item.dataAtualizacao`]="{ item }">
            {{
              item.dataCriacao === item.dataAtualizacao
                ? "-"
                : formatDate(item.dataAtualizacao)
            }}
            <!-- Exibindo a última atualização -->
          </template>
          <template v-slot:[`item.actions`]="{ item }">
            <div class="icons">
              <v-tooltip text="Visualizar" location="bottom" v-if="!isTitular">
                <template v-slot:activator="{ props }" v-if="!isTitular">
                  <v-btn
                    v-bind="props"
                    icon="mdi-eye"
                    elevation="0"
                    size="small"
                    @click="openEditModal(item)"
                  ></v-btn>
                </template>
              </v-tooltip>
              <v-tooltip text="Editar" location="bottom" v-if="isTitular">
                <template v-slot:activator="{ props }" v-if="isTitular">
                  <v-btn
                    v-bind="props"
                    icon="mdi-pencil"
                    elevation="0"
                    size="small"
                    @click="openEditModal(item)"
                  ></v-btn>
                </template>
              </v-tooltip>
              <v-tooltip text="Excluir" location="bottom">
                <template v-slot:activator="{ props }" v-if="isTitular">
                  <v-btn
                    style="color: #ef5350"
                    v-bind="props"
                    icon="mdi-delete"
                    elevation="0"
                    size="small"
                    @click="openDeleteModal(item)"
                  ></v-btn>
                </template>
              </v-tooltip>
            </div>
          </template>
        </v-data-table>
      </v-card>
      <v-btn
        v-if="isTitular"
        class="btnGreen btnAdd"
        rounded="0"
        @click="openAddModal"
        >+ ADICIONAR VÍDEOS</v-btn
      >
    </div>
    <v-snackbar
      v-model="snackbar"
      timeout="3000"
      elevation="24"
      :color="snackbarColor"
    >
      <v-icon left class="pr-2">{{ snackbarIcon }}</v-icon>
      {{ snackbarText }}
      <template v-slot:actions>
        <v-btn
          color="white"
          variant="text"
          @click="snackbar = false"
          icon="mdi-close"
        >
        </v-btn>
      </template>
    </v-snackbar>

    <!-- Modal for Add/Edit Video -->
    <v-dialog v-model="dialog" max-width="1000px">
      <v-card class="pa-2">
        <v-card-title>
          <span class="headline">{{ dialogTitle }}</span>
        </v-card-title>
        <v-form @submit.prevent="submitForm()" ref="formVideo">
          <v-card-text>
            <v-row>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  class="pb-0"
                  :readonly="!isTitular"
                  v-model="editedItem.titulo"
                  label="Título"
                  :rules="[rules.required, rules.titulo, rules.emoji]"
                  counter
                  maxlength="80"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  class="pb-0"
                  :readonly="!isTitular"
                  v-model="editedItem.descricao"
                  label="Descrição"
                  :rules="[rules.required, rules.emoji]"
                  counter
                  required
                  no-resize
                ></v-textarea>
              </v-col>
              <v-col cols="12" class="pt-0">
                <p>Vídeos</p>
                <UploadVideos
                  ref="uploadVideo"
                  :caminho="this.editedItem.caminho"
                  :titulo="editedItem.titulo"
                  :idArquivos="editedItem.idArquivos"
                  @update-deleted-urls="updateDeletedUrls"
                />
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="action">
            <v-btn class="btnCancel" rounded="0" text @click="closeDialog">{{
              isTitular ? "Cancelar" : "Fechar"
            }}</v-btn>
            <v-btn
              v-if="isTitular"
              class="btnGreen"
              rounded="0"
              text
              type="submit"
              :disabled="disabledButton"
              >Salvar</v-btn
            >
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
    <!-- Modal for Delete Confirmation -->
    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card class="pa-2">
        <v-card-title class="headline">Excluir</v-card-title>
        <v-card-text>
          Você realmente deseja excluir o(s) vídeo(s)
          <strong>{{ deletedItem.titulo }}</strong
          >?
        </v-card-text>
        <v-card-actions class="action">
          <v-btn class="btnCancel" rounded="0" text @click="closeDeleteDialog"
            >Cancelar</v-btn
          >
          <v-btn
            class="btnGreen"
            rounded="0"
            text
            @click="deleteItemConfirmed(deletedItem.idArquivos)"
            >Excluir</v-btn
          >
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import UploadVideos from "./UploadVideos.vue";
import axios from "axios";
import { format, subHours } from "date-fns";

export default {
  data() {
    return {
      search: "",
      baseUrl: "http://localhost:8181/",
      dialog: false,
      deleteDialog: false,
      dialogTitle: "",
      editedItem: {
        idArquivos: "",
        titulo: "",
        descricao: "",
        caminho: "",
      },
      deletedUrls: [],
      deletedItem: null,
      headers: [
        { title: "Arquivo", align: "start", value: "arquivo", sortable: false },
        { title: "Título", align: "start", value: "titulo", sortable: true },
        { title: "Data de Criação", value: "dataCriacao", sortable: true },
        { title: "Última Alteração", value: "dataAtualizacao", sortable: true },
        { title: "", value: "actions", align: "center", sortable: false },
      ],
      videos: [],
      editedIndex: -1,
      defaultItem: {
        idArquivos: "",
        titulo: "",
        descricao: "",
        caminho: "",
      },
      pasta: {
        id: this.$route.params.id,
        titulo: "",
      },
      rules: {
        required: (value) => !!value || "Este campo é obrigatório.",
        titulo: (value) =>
          value.length <= 80 || "Tamanho máximo do título: 80 caracteres.",
        emoji: (value) =>
          !/(?:\p{Extended_Pictographic})/u.test(value) ||
          "Caractere inválido.",
      },
      snackbar: false,
      snackbarText: "",
      snackbarColor: "",
      disabledButton: false,
      isTitular: false,
    };
  },
  async created() {
    await this.folderExists();
    await this.checkTitular();
  },
  methods: {
    async folderExists() {
      try {
        const response = await axios.get(
          "http://localhost:8181/pastas.php?acao=folderExists",
          {
            withCredentials: true,
          }
        );
        const jsonString = response.data.match(/\[.*\]/)[0];
        const folders = JSON.parse(jsonString);
        const urlPath = window.location.pathname;
        const idPasta = urlPath.split("/").pop();

        // Verificar se idPasta existe na lista de pastas
        const hasAccess = folders.some((folder) => folder.idPasta === idPasta);

        // Se o idPasta não estiver na lista, redirecionar para a página inicial
        if (!hasAccess) {
          this.$router.push("../pastas");
        }
      } catch (error) {
        console.error(error);
      }
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
    updateDeletedUrls(newDeletedUrls) {
      this.deletedUrls = newDeletedUrls;
    },
    openAddModal() {
      this.dialogTitle = "Adicionar Vídeos";
      this.editedItem = Object.assign({}, this.defaultItem);
      this.dialog = true;
      this.deletedUrls = [];
    },
    openEditModal(item) {
      if(this.isTitular){
        this.dialogTitle = "Editar Vídeos";
      }else{
        this.dialogTitle = "Visualizar Vídeos";
      }
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
      this.deletedUrls = [];
    },
    openDeleteModal(item) {
      this.deletedItem = item;
      this.deleteDialog = true;
    },
    closeDialog() {
      this.dialog = false;
    },
    closeDeleteDialog() {
      this.deleteDialog = false;
    },
    async submitForm() {
      const videoFiles = this.$refs.uploadVideo.getVideoFiles();

      if (!Array.isArray(videoFiles)) {
        console.error("getVideoFiles não retornou um array.");
        return;
      }

      const isValid = await this.$refs.formVideo.validate();

      if (!isValid.valid || videoFiles.length == 0) {
        if (videoFiles.length == 0) {
          this.snackbarText = "Pelo menos um vídeo deve ser anexado.";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
          this.disabledButton = false;
        }
        return;
      }

      const formData = new FormData();
      videoFiles.forEach((file, index) => formData.append("arquivo[]", file));
      formData.append("idArquivos", this.editedItem.idArquivos);
      formData.append("tituloArquivo", this.editedItem.titulo);
      formData.append("descricaoArquivo", this.editedItem.descricao);
      formData.append("idPasta", this.pasta.id);
      formData.append("urlsToDelete", this.deletedUrls);

      this.disabledButton = true;

      await axios
        .post(this.baseUrl + "videos.php", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((response) => {
          this.closeDialog();
          this.fetchVideos();
          this.snackbarText = "Vídeo(s) salvo(s) com sucesso";
          this.snackbarColor = "success";
          this.snackbarIcon = "mdi-check-circle";
          this.snackbar = true;
          this.deletedUrls = [];
          this.disabledButton = false;
        })
        .catch((error) => {
          console.error(error);
          this.snackbarText = "Erro ao salvar vídeo(s)";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
          this.disabledButton = false;
        });
    },
    getImageCount(caminho) {
      if (!caminho) return 0; // Retorna 0 se não houver caminho
      return caminho.split(",").length; // Conta o número de imagens separadas por vírgula
    },
    getFirstImage(arquivo) {
      if (arquivo) {
        // Separa as imagens por vírgula e retorna a primeira
        return arquivo.split(",")[0].trim();
      }
      return ""; // Retorna vazio se não houver imagem
    },
    getImageUrl(caminho) {
      // Remove barras invertidas (\) e constrói o caminho completo da imagem
      return this.baseUrl + caminho.replace(/\\/g, "/");
    },
    async fetchVideos() {
      try {
        const response = await axios.get(this.baseUrl + "arquivos.php", {
          params: { idPasta: this.pasta.id },
        });
        this.videos = response.data;
      } catch (error) {
        console.error("Error:", error);
        this.snackbarText = "Erro ao carregar os arquivos";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
      }
    },
    async fetchFolder() {
      try {
        const response = await axios.get(this.baseUrl + "pastas.php", {
          params: {
            idPasta: this.pasta.id,
          },
        });
        const jsonString = response.data.match(/\[.*\]/)[0];
        this.pasta.titulo = JSON.parse(jsonString)[0].titulo;
      } catch (error) {
        console.error("Error:", error);
      }
    },
    onVideosAdded(videos) {
      this.editedItem.videos = videos.map((video) => video.file);
    },
    navigateTo(route) {
      this.$router.push(route).catch((err) => {
        console.error(err);
      });
    },
    formatDate(date) {
      // Subtrai 3 horas da data
      const adjustedDate = subHours(new Date(date), 3);
      // Formata a data no formato dd/mm/aaaa hh:mm
      return format(adjustedDate, "dd/MM/yyyy HH:mm");
    },
    truncateTitle(title) {
      return title.length > 50 ? title.substring(0, 48) + "..." : title;
    },
    searchVideos() {
      if (this.search.trim() === "") {
        this.fetchVideos();
      } else {
        this.videos = this.videos.filter((item) =>
          item.titulo.toLowerCase().includes(this.search.toLowerCase())
        );
      }
    },
    async deleteItemConfirmed(id) {
      await axios
        .delete(this.baseUrl + "arquivos.php", {
          params: { idArquivos: id },
        })
        .then((response) => {
          this.closeDeleteDialog();
          this.fetchVideos();
          this.snackbarText = "Vídeo(s) excluído(s) com sucesso";
          this.snackbarColor = "success";
          this.snackbarIcon = "mdi-check-circle";
          this.snackbar = true;
        })
        .catch((error) => {
          console.error("Error:", error);
          this.snackbarText = "Erro ao excluir vídeo(s)";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
        });
    },
  },
  mounted() {
    this.fetchVideos();
    this.fetchFolder();
  },
  watch: {
    search() {
      this.searchVideos();
    },
  },
  components: { UploadVideos },
};
</script>
<style scoped>
.title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 99.9%;
}
.v-card {
  display: flex;
  flex-direction: column;
}
.action {
  display: flex;
  justify-content: center;
}
.v-card-title {
  display: flex;
}
.container {
  display: flex;
  flex-direction: column;
  padding-bottom: 76px;
}
.v-data-table {
  white-space: nowrap;
}
.icons {
  display: flex;
  justify-content: center;
}
.v-card-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.btnGreen {
  display: block;
  color: #fff;
  border: none;
  letter-spacing: 0.8px;
  font-size: 14px;
  padding: 5px 15px;
  transition: all 0.25s ease;
  text-transform: uppercase;
  background-color: #91c141;
}
.btnGreen:hover {
  background-color: #6d9232;
}
.btnCancel {
  display: block;
  border: none;
  letter-spacing: 0.8px;
  font-size: 14px;
  padding: 5px 15px;
  transition: all 0.25s ease;
  text-transform: uppercase;
}
.btnAdd {
  align-self: flex-end;
  margin-top: 16px;
}
.relative {
  position: relative; /* Define a posição relativa para o contêiner */
}
.image-count {
  position: absolute; /* Posição absoluta para sobrepor a imagem */
  bottom: 4px; /* Distância do fundo */
  right: 4px; /* Distância da direita */
  background-color: rgba(
    0,
    0,
    0,
    0.6
  ); /* Fundo semi-transparente para contraste */
  color: white; /* Cor do texto */
  padding: 2px 5px; /* Espaçamento interno */
  border-radius: 3px; /* Bordas arredondadas */
  font-weight: bold; /* Negrito */
  z-index: 2;
}
@media screen and (max-width: 767px) {
  .header {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .header .v-text-field {
    width: 100%;
  }
  .title {
    padding-bottom: 16px;
  }
  .btnAdd {
    align-self: center;
  }
}
</style>
