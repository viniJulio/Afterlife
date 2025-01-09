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
          ></v-text-field>
        </v-card-title>
        <v-data-table
          :headers="headers"
          :items="imageFile"
          items-per-page-text="itens por página"
          page-text="{0}-{1} de {2}"
          :search="search"
          class="elevation-1"
          no-data-text="Sem imagens salvas"
        >
          <template v-slot:[`item.arquivo`]="{ item }">
            <v-img
              :src="getImageUrl(getFirstImage(item.caminho))"
              :width="110"
              :height="70"
              cover
              class="relative"
              @click="openPreviewDialog()"
            >
              <span class="image-count">
                {{ getImageCount(item.caminho) }}
              </span>
            </v-img>
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
            <!-- Exibindo a última atualização, se houver -->
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
        >+ ADICIONAR IMAGENS</v-btn
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

    <!--modal para adicionar/editar documentos-->
    <v-dialog v-model="dialog" max-width="1000px">
      <v-card class="pa-2">
        <v-card-title>
          <span class="headline">{{ dialogTitle }}</span>
        </v-card-title>
        <v-form @submit.prevent="submitForm()" ref="formImagem">
          <v-card-text>
            <v-row>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  class="pb-0"
                  :readonly="!isTitular"
                  v-model="editedItem.titulo"
                  label="Título"
                  counter
                  maxlength="80"
                  :rules="[rules.required, rules.titulo, rules.emoji]"
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
                <p>Imagens</p>
                <UploadImage
                  ref="uploadImage"
                  :titulo="editedItem.titulo"
                  :caminho="this.editedItem.caminho"
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

    <!-- modal para confirmação de exclusão de senha -->
    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card class="pa-2">
        <v-card-title class="headline">Excluir</v-card-title>
        <v-card-text>
          Você realmente deseja excluir a(s) imagem(s)
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
import UploadImage from "./UploadImage.vue";
import axios from "axios";
import { format, subHours } from "date-fns";

export default {
  components: { UploadImage },
  data() {
    return {
      search: "",
      dialog: false,
      deleteDialog: false,
      previewDialog: false,
      dialogTitle: "",
      editedItem: {
        idArquivos: "",
        titulo: "",
        descricao: "",
        caminho: "",
      },
      deletedItem: null,
      deletedUrls: [],
      headers: [
        { title: "Arquivo", align: "start", value: "arquivo", sortable: false },
        { title: "Título", value: "titulo", sortable: true },
        { title: "Data de Criação", value: "dataCriacao", sortable: true },
        { title: "Última Alteração", value: "dataAtualizacao", sortable: true },
        { title: "", value: "actions", align: "center", sortable: false },
      ],
      imageFile: [],
      baseUrl: "http://localhost:8181/",
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
      isTitular: null,
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
    getImageCount(caminho) {
      if (!caminho) return 0; // Retorna 0 se não houver caminho
      return caminho.split(",").length; // Conta o número de imagens separadas por vírgula
    },
    async submitForm() {
      const imageFiles = this.$refs.uploadImage.getImageFiles();

      if (!Array.isArray(imageFiles)) {
        console.error("getImageFiles não retornou um array.");
        return;
      }

      const isValid = await this.$refs.formImagem.validate();

      if (!isValid.valid || imageFiles.length == 0) {
        if (imageFiles.length == 0) {
          this.snackbarText = "Pelo menos uma imagem deve ser anexada.";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
          this.disabledButton = false;
        }
        return;
      }

      const formData = new FormData();
      imageFiles.forEach((file, index) => formData.append("arquivo[]", file));
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
          this.fetchImages();
          this.snackbarText = "Imagem(s) salva(s) com sucesso";
          this.snackbarColor = "success";
          this.snackbarIcon = "mdi-check-circle";
          this.snackbar = true;
          this.deletedUrls = [];
          this.disabledButton = false;
        })
        .catch((error) => {
          console.error(error);
          this.snackbarText = "Erro ao salvar imagem(s)";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
          this.disabledButton = false;
        });
    },
    getFirstImage(arquive) {
      if (arquive) {
        // Separa as imagens por vírgula e retorna a primeira
        return arquive.split(",")[0].trim();
      }
      return ""; // Retorna vazio se não houver imagem
    },
    getImageUrl(caminho) {
      // Remove barras invertidas (\) e constrói o caminho completo da imagem
      return this.baseUrl + caminho.replace(/\\/g, "/");
    },
    openAddModal() {
      this.dialogTitle = "Adicionar Imagens";
      this.editedItem = Object.assign({}, this.defaultItem);
      this.dialog = true;
    },
    openEditModal(item) {
      if (this.isTitular) {
        this.dialogTitle = "Editar Imagens";
      } else {
        this.dialogTitle = "Visualizar Imagens";
      }
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
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
    openPreviewDialog() {
      this.previewDialog = true;
    },
    closePreviewDialog() {
      this.previewDialog = false;
    },
    async deleteItemConfirmed(id) {
      await axios
        .delete("http://localhost:8181/arquivos.php", {
          params: { idArquivos: id },
        })
        .then((response) => {
          this.closeDeleteDialog();
          this.fetchImages();
          this.snackbarText = "Imagem(s) excluída(s) com sucesso";
          this.snackbarColor = "success";
          this.snackbarIcon = "mdi-check-circle";
          this.snackbar = true;
        })
        .catch((error) => {
          console.error("Error:", error);
          this.snackbarText = "Erro ao excluir imagem(s)";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
        });
    },
    navigateTo(route) {
      this.$router
        .push(route)
        .then(() => {
          console.log("Navegação bem-sucedida para:", route);
        })
        .catch((err) => {
          console.error("Erro ao navegar:", err);
        });
    },
    truncateTitle(title) {
      return title.length > 50 ? title.substring(0, 48) + "..." : title;
    },
    formatDate(date) {
      // Subtrai 3 horas da data
      const adjustedDate = subHours(new Date(date), 3);
      // Formata a data no formato dd/mm/aaaa hh:mm
      return format(adjustedDate, "dd/MM/yyyy HH:mm");
    },
    searchImages() {
      if (this.search.trim() === "") {
        this.fetchImages();
      } else {
        this.imageFile = this.imageFile.filter((item) =>
          item.titulo.toLowerCase().includes(this.search.toLowerCase())
        );
      }
    },
    async fetchImages() {
      try {
        const response = await axios.get(this.baseUrl + "arquivos.php", {
          params: { idPasta: this.pasta.id },
        });
        this.imageFile = response.data;
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
  },
  mounted() {
    this.fetchImages();
    this.fetchFolder();
  },
  watch: {
    search() {
      this.searchImages();
    },
  },
};
</script>

<style scoped>
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
}
</style>
