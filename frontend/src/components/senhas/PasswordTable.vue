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
          :items="senhas"
          :search="search"
          class="elevation-1"
          items-per-page-text="itens por página"
          pageText="{0}-{1} de {2}"
          no-data-text="Sem senhas salvas"
        >
          <template v-slot:[`item.icon`]="{ item }">
            <v-icon> mdi-shield-lock-outline </v-icon>
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
      <v-btn v-if="isTitular" class="btnGreen btnAdd" rounded="0" @click="openAddModal"
        >+ ADICIONAR SENHA</v-btn
      >
    </div>

    <!--modal para adicionar/editar senha-->
    <v-dialog v-model="dialog" max-width="500px">
      <v-card class="pa-2">
        <v-card-title>
          <span class="headline">{{ dialogTitle }}</span>
        </v-card-title>
        <v-form ref="formSenha" @submit.prevent="submitForm()">
          <v-card-text>
            <v-row>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  v-model="editedItem.titulo"
                  :readonly="!isTitular"
                  label="Título"
                  required
                  :rules="[rules.required, rules.titulo, rules.emoji]"
                  counter
                  maxlength="80"
                ></v-text-field>
              </v-col>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  v-model="editedItem.descricao"
                  :readonly="!isTitular"
                  label="Descrição"
                  required
                  :rules="[rules.required, rules.emoji]"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  label="Senha"
                  required
                  :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                  :type="visible ? 'text' : 'password'"
                  v-model="editedItem.senha"
                  :readonly="!isTitular"
                  :rules="[rules.required, rules.emoji]"
                  @click:append-inner="visible = !visible"
                  counter
                  maxlength="255"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="action">
            <v-btn class="btnCancel" rounded="0" text @click="closeDialog"
              >{{
              isTitular ? "Cancelar" : "Fechar"
            }}</v-btn
            >
            <v-btn v-if="isTitular" class="btnGreen" rounded="0" text type="submit"
              >Salvar</v-btn
            >
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>

    <!-- modal para visualizar senha -->
    <v-dialog v-model="verSenhaDialog" max-width="500px">
      <v-card class="pa-2">
        <v-card-title class="headline">Visualizar Senha</v-card-title>
        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  :value="senhaTexto"
                  label="Clique para visualizar a senha"
                  readonly
                ></v-text-field>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>
        <v-card-actions class="action">
          <v-btn class="btnGreen" rounded="0" text @click="closeVerSenhaDialog"
            >Fechar</v-btn
          >
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- modal para confirmação de exclusão de senha -->
    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card class="pa-2">
        <v-card-title class="headline">Excluir</v-card-title>
        <v-card-text>
          Você realmente deseja excluir a senha
          <strong>{{ deletedItem.titulo }}</strong
          >?
        </v-card-text>
        <v-card-actions class="action">
          <v-btn class="btnCancelar" rounded="0" text @click="closeDeleteDialog"
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
</template>

<script>
import axios from "axios";
import { format, subHours } from "date-fns";

export default {
  data() {
    return {
      search: "",
      visible: false,
      dialog: false,
      verSenhaDialog: false,
      deleteDialog: false,
      dialogTitle: "",
      editedItem: {
        id: "",
        titulo: "",
        descricao: "",
        senha: "",
      },
      senhaTexto: "",
      deletedItem: null,
      headers: [
        { title: "", value: "icon", sortable: false },
        { title: "Título", align: "start", value: "titulo", sortable: true },
        { title: "Data de Criação", value: "dataCriacao", sortable: true },
        { title: "Última Alteração", value: "dataAtualizacao", sortable: true },
        { title: "", value: "actions", sortable: false },
      ],
      senhas: [],
      defaultItem: {
        id: "",
        titulo: "",
        descricao: "",
        senha: "",
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
    truncateTitle(title) {
      return title.length > 50 ? title.substring(0, 48) + "..." : title;
    },
    formatDate(date) {
      // Subtrai 3 horas da data
      const adjustedDate = subHours(new Date(date), 3);
      // Formata a data no formato dd/mm/aaaa hh:mm
      return format(adjustedDate, "dd/MM/yyyy HH:mm");
    },
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
    openAddModal() {
      this.dialogTitle = "Cadastrar Senha";
      this.editedItem = Object.assign({}, this.defaultItem);
      this.dialog = true;
    },
    openEditModal(item) {
      if(this.isTitular){
        this.dialogTitle = "Editar Senha";
      }else{
        this.dialogTitle = "Visualizar Senha";
      }
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },
    openVerSenhaModal(item) {
      this.senhaTexto = item.senha;
      this.verSenhaDialog = true;
    },
    openDeleteModal(item) {
      this.deletedItem = Object.assign({}, item);
      this.deleteDialog = true;
    },
    closeDialog() {
      this.dialog = false;
    },
    closeVerSenhaDialog() {
      this.verSenhaDialog = false;
    },
    closeDeleteDialog() {
      this.deleteDialog = false;
    },
    async submitForm() {
      const isValid = await this.$refs.formSenha.validate();

      if (!isValid.valid) {
        return;
      }

      try {
        const response = await axios.post("http://localhost:8181/senhas.php", {
          idArquivos: this.editedItem.idArquivos ?? "",
          titulo: this.editedItem.titulo,
          descricao: this.editedItem.descricao,
          senha: this.editedItem.senha,
          fkIdPasta: this.pasta.id,
        });

        console.log(response);

        if (response.status !== 200) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }

        this.fetchSenhas();
        this.closeDialog();
        this.snackbarText = this.editedItem.idArquivos
          ? "Senha alterada com sucesso"
          : "Senha salva com sucesso";
        this.snackbarColor = "success";
        this.snackbarIcon = "mdi-check-circle";
        this.snackbar = true;
      } catch (error) {
        console.error("Error:", error);
        this.disabledButton = false;
        this.snackbarText = "Erro ao salvar senha";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
      }
    },
    async fetchSenhas() {
      try {
        const response = await axios.get("http://localhost:8181/senhas.php", {
          params: {
            idPasta: this.pasta.id,
          },
        });
        const jsonString = response.data.match(/\[.*\]/)[0];
        this.senhas = JSON.parse(jsonString);
        this.deletedItem = Object.assign({}, this.defaultItem);
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
        const response = await axios.get("http://localhost:8181/pastas.php", {
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
    async deleteItemConfirmed(id) {
      try {
        const response = await axios.delete(
          "http://localhost:8181/senhas.php",
          {
            data: { idArquivos: id },
          }
        );

        if (response.status !== 200) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }

        this.snackbarText = "Senha excluída com sucesso";
        this.snackbarColor = "success";
        this.snackbarIcon = "mdi-check-circle";
        this.snackbar = true;
      } catch (error) {
        console.error("Error:", error);
        this.snackbarText = "Erro ao excluir senha";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
      }
      this.fetchSenhas();
      this.closeDeleteDialog();
    },
    navigateTo(route) {
      this.$router.push(route).catch((err) => {
        console.error(err);
      });
    },
  },
  mounted() {
    this.fetchSenhas();
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
  display: flex;
  justify-content: center;
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
  .btnGreen {
    align-self: center;
  }
  .btnAdd {
    align-self: center;
  }
}
</style>
