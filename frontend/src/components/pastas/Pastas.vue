<template>
  <v-container>
    <div class="container" style="height: auto">
      <p class="title">Armazenamento</p>
      <div class="bottons">
        <v-btn
          v-if="isTitular"
          class="btnGreen"
          rounded="0"
          @click="openAddModal"
          >+ NOVA PASTA</v-btn
        >
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Pesquisar"
          single-line
          hide-details
          @input="searchFolders"
        ></v-text-field>
      </div>
      <v-row class="py-6">
        <v-col
          cols="12"
          sm="6"
          md="4"
          v-for="(item, index) in items"
          :key="index"
        >
          <v-card
            hover
            rounded="0"
            class="card"
            @click="navigateTo(item.route)"
          >
            <v-row class="align-start">
              <v-col cols="9">
                <v-card-title class="pa-0">{{ item.titulo }}</v-card-title>
              </v-col>
              <v-col cols="3" class="d-flex justify-end align-center">
                <div class="icons">
                  <v-tooltip v-if="isTitular" text="Editar" location="bottom">
                    <template v-slot:activator="{ props }">
                      <v-btn
                        v-bind="props"
                        icon="mdi-pencil"
                        elevation="0"
                        class="btnIconWhite"
                        size="small"
                        @click.stop="openEditModal(item)"
                      ></v-btn>
                    </template>
                  </v-tooltip>
                  <v-tooltip v-if="isTitular" text="Excluir" location="bottom">
                    <template v-slot:activator="{ props }">
                      <v-btn
                        v-bind="props"
                        icon="mdi-delete"
                        elevation="0"
                        class="btnIconWhite"
                        size="small"
                        @click.stop="openDeleteModal(item)"
                      ></v-btn>
                    </template>
                  </v-tooltip>
                </div>
              </v-col>
            </v-row>
            <v-row class="align-end">
              <v-col cols="12">
                <p class="text">{{ item.descricao }}</p>
              </v-col>
            </v-row>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <v-snackbar
      v-model="snackbar"
      timeout="2500"
      elevation="24"
      :color="snackbarColor"
    >
      <v-icon left class="pe-2">{{ snackbarIcon }}</v-icon>
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

    <!-- Modal for Add/Edit Folder -->
    <v-dialog v-model="dialog" max-width="500px">
      <v-card class="pa-2">
        <v-card-title>
          <span class="headline">{{ dialogTitle }}</span>
        </v-card-title>
        <v-card-text>
          <v-form ref="formFolder">
            <v-row>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  v-model="editedItem.titulo"
                  label="Título"
                  :rules="[rules.required, rules.titulo, rules.emoji]"
                  required
                  counter
                  maxlength="80"
                ></v-text-field>
              </v-col>
              <v-col cols="12" class="pb-0">
                <v-select
                  v-model="editedItem.tipo"
                  :items="Object.keys(tipoOptions)"
                  label="Tipo"
                  :rules="[rules.required, rules.emoji]"
                  required
                  :readonly="dialogTitle === 'Editar Pasta'"
                ></v-select>
              </v-col>
              <v-col cols="12" class="pb-0">
                <v-textarea
                  v-model="editedItem.descricao"
                  label="Descrição"
                  :rules="[rules.required, rules.emoji]"
                  required
                  counter
                  no-resize
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions class="action">
          <v-btn class="btnCancel" rounded="0" text @click="closeDialog"
            >Cancelar</v-btn
          >
          <v-btn
            class="btnGreen"
            rounded="0"
            text
            @click="saveItem"
            :disabled="disabledButton"
            >Confirmar</v-btn
          >
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal for Delete Confirmation -->
    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card class="pa-2">
        <v-card-title class="headline">Excluir</v-card-title>
        <v-card-text>
          Você realmente deseja excluir a pasta
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
            @click="deleteItemConfirmed(deletedItem.idPasta)"
            >Excluir</v-btn
          >
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import axios from "axios";
import router from "@/router";

export default {
  data() {
    return {
      search: "",
      dialog: false,
      deleteDialog: false,
      dialogTitle: "",
      editedItem: {
        idPasta: "",
        titulo: "",
        tipo: "",
        descricao: "",
      },
      deletedItem: null,
      items: null,
      originalItems: null,
      rules: {
        required: (value) => !!value || "Este campo é obrigatório.",
        titulo: (value) =>
          value.length <= 80 || "Tamanho máximo do título: 80 caracteres.",
        emoji: (value) =>
          !/(?:\p{Extended_Pictographic})/u.test(value) ||
          "Caractere inválido.",
      },
      defaultItem: {
        idPasta: "",
        titulo: "",
        tipo: "",
        descricao: "",
      },
      snackbar: false,
      snackbarText: "",
      snackbarColor: "",
      snackbarIcon: "",
      tipoOptions: {
        Imagem: "imagem",
        Documento: "documento",
        Senha: "senha",
        Áudio: "audio",
        Texto: "texto",
        Vídeo: "video",
      },
      tipoReverse: {
        imagem: "Imagem",
        documento: "Documento",
        senha: "Senha",
        audio: "Áudio",
        texto: "Texto",
        video: "Vídeo",
      },
      search: "",
      disabledButton: false,
      isTitular: null,
    };
  },
  async created() {
    await this.isLoggedIn();
    await this.checkTitular();
  },
  methods: {
    async isLoggedIn() {
      try {
        const response = await axios.get(
          "http://localhost:8181/pessoas.php?acao=isLoggedIn",
          {
            withCredentials: true,
          }
        );

        // Extrair a parte do JSON a partir da resposta
        const jsonString = response.data.match(/\{.*\}/)[0];
        const data = JSON.parse(jsonString);

        if (data.status == "erro") {
          router.push({ path: "/" });
        }
      } catch (error) {
        console.error(error);
        router.push({ path: "/" });
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
    openAddModal() {
      this.dialogTitle = "Nova Pasta";
      this.editedItem = Object.assign({}, this.defaultItem);
      this.dialog = true;
    },
    openEditModal(item) {
      this.dialogTitle = "Editar Pasta";
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
    async saveItem() {
      const isValid = await this.$refs.formFolder.validate();

      if (!isValid.valid) {
        return;
      }

      this.disabledButton = true;

      try {
        const response = await axios.post(
          "http://localhost:8181/pastas.php",
          {
            idPasta: this.editedItem.idPasta ?? "",
            titulo: this.editedItem.titulo,
            tipo: this.tipoOptions[this.editedItem.tipo],
            descricao: this.editedItem.descricao,
          },
          {
            withCredentials: true,
          }
        );

        this.disabledButton = false;

        if (response.status !== 200) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }

        this.fetchFolders();
        this.closeDialog();

        this.snackbarText = `Pasta ${
          this.editedItem.idPasta ? "alterada" : "criada"
        } com sucesso`;
        this.snackbarColor = "success";
        this.snackbarIcon = "mdi-check-circle";
        this.snackbar = true;
      } catch (error) {
        console.error("Error:", error);
        this.disabledButton = false;
        this.snackbarText = "Erro ao salvar pasta";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
      }
    },
    async fetchFolders() {
      this.search = "";
      try {
        const response = await axios.get("http://localhost:8181/pastas.php", {
          withCredentials: true,
        });
        const jsonString = response.data.match(/\[.*\]/)[0];
        const items = JSON.parse(jsonString).map((item) => ({
          ...item,
          tipo: this.tipoReverse[item.tipo],
          route: `${item.tipo}/${item.idPasta}`,
        }));
        this.items = items;
        this.originalItems = [...items];
      } catch (error) {
        console.error("Error:", error);
        this.snackbarText = "Erro ao carregar as pastas";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
      }
    },
    async deleteItemConfirmed(id) {
      try {
        const response = await axios.delete(
          "http://localhost:8181/pastas.php",
          {
            data: { idPasta: id },
          }
        );

        if (response.status !== 200) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }

        this.snackbarText = "Pasta excluída com sucesso";
        this.snackbarColor = "success";
        this.snackbarIcon = "mdi-check-circle";
        this.snackbar = true;
      } catch (error) {
        console.error("Error:", error);
        this.snackbarText = "Erro ao excluir pasta";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
      }
      this.fetchFolders();
      this.closeDeleteDialog();
    },
    navigateTo(route) {
      this.$router.push(route);
    },
    searchFolders() {
      if (this.search.trim() === "") {
        this.fetchFolders();
      } else {
        this.items = this.originalItems.filter((item) =>
          item.titulo.toLowerCase().includes(this.search.toLowerCase())
        );
      }
    },
  },
  mounted() {
    this.fetchFolders();
  },
  watch: {
    search() {
      this.searchFolders();
    },
  },
};
</script>

<style scoped>
.container {
  display: flex;
  flex-direction: column;
}
.action {
  align-self: center;
}
.title {
  font-size: 20px;
  padding: 16px 0;
  font-weight: 500;
}
.bottons {
  display: flex;
  gap: 16px;
  align-self: flex-end;
  width: 500px;
  justify-content: center;
  align-items: center;
}
.btnGreen {
  display: block;
  color: #fff;
  border: none;
  letter-spacing: 0.8px;
  font-size: 14px;
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
.card {
  height: 200px;
  background-color: #617a95 !important;
  color: white !important;
  padding: 10px 16px 16px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.card:hover {
  background-color: #4d6279 !important;
}
.icons {
  display: flex;
  justify-content: center;
}
.btnIconWhite {
  background-color: transparent !important;
  color: white !important;
  padding: 0;
}
.text {
  font-size: 12px;
  margin-top: auto;
  font-weight: regular;
}
@media screen and (max-width: 767px) {
  .title {
    text-align: center;
  }
  .bottons {
    gap: 16px;
    align-self: center;
    max-width: 100%;
    flex-direction: column-reverse;
    align-items: normal;
  }
  .btnGreen {
    align-self: center;
    padding: 5px 15px;
  }
}
</style>
