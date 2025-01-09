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
          :items="documentFile"
          items-per-page-text="itens por página"
          pageText="{0}-{1} de {2}"
          :search="search"
          class="elevation-1"
          no-data-text="Sem documentos salvos"
        >
          <template v-slot:[`item.titulo`]="{ item }">
            {{ item.titulo }}
            <!-- Exibindo o título -->
          </template>
          <template v-slot:[`item.datecreate`]="{ item }">
            {{ formatDate(item.datecreate) }}

            <!-- Exibindo a data de criação -->
          </template>
          <template v-slot:[`item.lastupdate`]="{ item }">
            {{
              item.datecreate === item.lastupdate
                ? "-"
                : formatDate(item.lastupdate)
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
              <v-tooltip text="Excluir" location="bottom" v-if="isTitular">
                <template v-slot:activator="{ props }">
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
        >+ ADICIONAR DOCUMENTOS</v-btn
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
      <v-form @submit.prevent="onSubmit" ref="formDocumento">
        <v-card class="pa-2">
          <v-card-title>
            <span class="headline">{{ dialogTitle }}</span>
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  class="pb-0"
                  v-model="editedItem.title"
                  label="Título"
                  :rules="[rules.required, rules.titulo, rules.emoji]"
                  counter
                  :readonly="!isTitular"
                  required
                  maxlength="80"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  class="pb-0"
                  v-model="editedItem.description"
                  label="Descrição"
                  :rules="[rules.required, rules.emoji]"
                  counter
                  :readonly="!isTitular"
                  required
                  no-resize
                ></v-textarea>
              </v-col>
              <v-col cols="12" class="py-0">
                <p>Documentos</p>
                <UploadDocument
                  ref="UploadDocument"
                  :caminho="editedItem.caminho"
                  :titulo="editedItem.title"
                  :idArquivos="editedItem.idArquivos"
                  @update-deleted-urls="updateDeletedUrls"
                />
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="action">
            <v-btn class="btnCancel" rounded="0" text @click="closeDialog"
              >{{ isTitular ? 'Cancelar' : 'Fechar' }}</v-btn
            >
            <v-btn
              v-if="isTitular"
              class="btnGreen"
              rounded="0"
              type="submit"
              text
              :disabled="disabledButton"
              >Salvar</v-btn
            >
          </v-card-actions>
        </v-card>
      </v-form>
    </v-dialog>

    <!-- modal para confirmação de exclusão de senha -->
    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card class="pa-2">
        <v-card-title class="headline">Excluir</v-card-title>
        <v-card-text>
          Você realmente deseja excluir o(s) documento(s)
          <strong>{{ deletedItem.title }}</strong
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
import UploadDocument from "./UploadDocument.vue";
import axios from "axios";
import { format, subHours } from "date-fns";

export default {
  components: { UploadDocument },
  props: {
    title: {
      type: String,
      default: "Editar Documentos",
    },
  },
  data() {
    return {
      search: "",
      dialog: false,
      deleteDialog: false,
      dialogTitle: "",
      editedItem: {
        idArquivos: "",
        title: "",
        description: "",
        caminho: "",
      },
      deletedItem: null,
      deletedUrls: [],
      headers: [
        { title: "Título", align: "start", value: "title" },
        { title: "Data de Criação", value: "datecreate" },
        { title: "Última Alteração", value: "lastupdate" },
        { title: "", value: "actions", sortable: false },
      ],
      documentFile: [],
      baseUrl: "http://localhost:8181/",
      editedIndex: -1,
      types: ["documento"],
      defaultItem: {
        idArquivos: "",
        title: "",
        description: "",
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
      isTitular: null,
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
    async fetchDocument() {
      const urlPath = window.location.pathname;
      const idPasta = urlPath.split("/").pop();

      try {
        const response = await axios.get(
          `http://localhost:8181/arquivos.php?idPasta=${idPasta}`
        );
        const jsonResponse = response.data;
        if (jsonResponse) {
          this.documentFile = jsonResponse.map((doc) => {
            return {
              idArquivos: doc.idArquivos,
              title: doc.titulo,
              description: doc.descricao,
              datecreate: doc.dataCriacao,
              lastupdate: doc.dataAtualizacao,
              caminho: doc.caminho,
            };
          });
        } else {
          console.error("Formato de resposta inválido:", response.data);
        }
      } catch (error) {
        console.error("Erro ao buscar dados:", error);
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
    updateDeletedUrls(newDeletedUrls) {
      this.deletedUrls = newDeletedUrls;
    },
    openAddModal() {
      this.dialogTitle = "Adicionar Documentos";
      this.editedItem = Object.assign({}, this.defaultItem);
      this.dialog = true;
    },
    openEditModal(item) {
      if(this.isTitular){
        this.dialogTitle = "Editar Documentos";
      }else{
        this.dialogTitle = "Visualizar Documentos";
      }
      this.editedItem = Object.assign({}, item);
      this.dialog = true;

      console.log(this.editedItem);
    },
    openDeleteModal(item) {
      this.deletedItem = item;
      this.deleteDialog = true;
    },
    closeDialog() {
      this.dialog = false;
    },
    closeViewPasswordDialog() {
      this.viewPasswordDialog = false;
    },
    closeDeleteDialog() {
      this.deleteDialog = false;
    },
    async onSubmit() {
      // debugger;

      const urlPath = window.location.pathname;
      const idPasta = urlPath.split("/").pop(); // Extrai o número da pasta, no caso, "2"

      const documentFiles = this.$refs.UploadDocument.getDocumentFiles();

      if (!Array.isArray(documentFiles)) {
        console.error("getDocumentFiles não retornou um array.");
        return;
      }

      const isValid = await this.$refs.formDocumento.validate();

      if (!isValid.valid || documentFiles.length == 0) {
        if (documentFiles.length == 0) {
          this.snackbarText = "Pelo menos um documento deve ser anexado.";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
          this.disabledButton = false;
        }
        return;
      }

      // Cria um FormData para enviar os arquivos
      const formData = new FormData();

      // Adiciona os documentos no array 'arquivo[]'
      documentFiles.forEach((file, index) =>
        formData.append("arquivo[]", file)
      );

      formData.append("idArquivos", this.editedItem.idArquivos);
      formData.append("tituloArquivo", this.editedItem.title); // 'title' foi renomeado para 'titulo'
      formData.append("descricaoArquivo", this.editedItem.description);
      formData.append("idPasta", idPasta);
      formData.append("urlsToDelete", this.deletedUrls);

      // Faz a requisição POST com multipart/form-data
      axios
        .post("http://localhost:8181/videos.php", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })

        .then((response) => {
          console.log(response.data);
          this.closeDialog();
          this.fetchDocument();
          this.deletedUrls = [];
        })
        .catch((error) => {
          console.error(error);
        });
    },
    formatDate(date) {
      if (!date) return ""; // Retorna vazio ou um valor padrão se a data estiver indefinida
      const adjustedDate = subHours(new Date(date), 3);
      return format(adjustedDate, "dd/MM/yyyy HH:mm");
    },
    deleteItemConfirmed(id) {
      fetch("http://localhost:8181/arquivos.php?idArquivos=" + id, {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
        },
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Erro na requisição DELETE");
          }
          return response.json();
        })
        .then((response) => {
          console.log("Arquivo(s) excluído(s) com sucesso!");
          this.closeDeleteDialog();
          this.fetchDocument();
        })
        .catch((error) => {
          console.error("Erro ao excluir arquivo(s):", error);
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
  },
  mounted() {
    this.fetchDocument();
    this.fetchFolder();
  },
};
</script>

<style scoped>
.v-card {
  display: flex;
  flex-direction: column;
}
.action {
  align-self: center;
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
</style>
