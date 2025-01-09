<template>
  <div class="container" style="margin: 0 20px;">
    <v-card class="cardWrapper">
      <v-card-title class="header">
        <div class="title">Usuários Cadastrados</div>
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
        :items="texts"
        :search="search"
        class="elevation-1"
        items-per-page-text="itens por página"
        pageText="{0}-{1} de {2}"
        no-data-text="Nenhum usuário cadastrado"
      >
        <template v-slot:[`item.cpf`]="{ item }">
          <span>
            {{ formatCPF(item.cpf) }}
          </span>
        </template>
        <template v-slot:[`item.telefone1`]="{ item }">
          <span>
            {{ formatTelefone(item.telefone1) }}
          </span>
        </template>
        <template v-slot:[`item.status`]="{ item }">
          <span :class="{ 'status-ativo': item.status === 'ativo' }">
            {{ capitalize(item.status) }}
          </span>
        </template>
        <template v-slot:[`item.actions`]="{ item }">
          <div class="icons">
            <v-tooltip text="Visualizar" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn
                  v-bind="props"
                  icon="mdi-eye"
                  elevation="0"
                  size="small"
                  @click="openViewModal(item)"
                ></v-btn>
              </template>
            </v-tooltip>
            <v-tooltip
              :text="item.acesso === 'bloqueado' ? 'Desbloquear' : 'Bloquear'"
              location="bottom"
            >
              <template v-slot:activator="{ props }">
                <v-btn
                  v-bind="props"
                  :icon="
                    hovered
                      ? item.acesso === 'desbloqueado'
                        ? 'mdi-cancel'
                        : 'mdi-check-bold'
                      : item.acesso === 'desbloqueado'
                      ? 'mdi-check-bold'
                      : 'mdi-cancel'
                  "
                  :style="
                    hovered
                      ? item.acesso === 'desbloqueado'
                        ? 'color: #ef5350'
                        : 'color: #66bb6a'
                      : item.acesso === 'desbloqueado'
                      ? 'color: #66bb6a'
                      : 'color: #ef5350'
                  "
                  elevation="0"
                  size="small"
                  @click="openBlockDialog(item)"
                  @mouseover="hovered = true"
                  @mouseleave="hovered = false"
                ></v-btn>
              </template>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
    </v-card>
  </div>

  <!-- Modal para ver titular e dependentes -->
  <v-dialog v-model="dialog" max-width="1000px">
    <v-card class="pa-1">
      <v-card-title>
        <span class="headline">{{ dialogTitle }}</span>
      </v-card-title>

      <!-- Dados do Titular -->
      <v-card-subtitle>Titular</v-card-subtitle>
      <v-card-text>
        <v-row>
          <v-col cols="12" sm="6">
            <p><strong>Nome:</strong> {{ selectedTitular.nome }}</p>
            <p>
              <strong>Nome Social:</strong>
              {{ formatField(selectedTitular.nomeSocial) }}
            </p>
            <p>
              <strong>CPF:</strong>
              {{ formatCPF(selectedTitular.cpf) }}
            </p>

            <p><strong>Email:</strong> {{ selectedTitular.email }}</p>
            <p>
              <strong>Data de Nascimento:</strong>
              {{ selectedTitular.dataNascimento }}
            </p>
            <p>
              <strong>Telefone 1:</strong>
              {{ formatTelefone(selectedTitular.telefone1) }}
            </p>
            <p>
              <strong>Telefone 2:</strong>
              {{ formatField(formatTelefone(selectedTitular.telefone2)) }}
            </p>
            <p>
              <strong>Status: </strong
              ><span
                :class="{ 'status-ativo': selectedTitular.status === 'ativo' }"
                >{{ capitalize(selectedTitular.status) }}</span
              >
            </p>
          </v-col>
          <v-col cols="12" sm="6">
            <p><strong>CEP:</strong> {{ formatCEP(selectedTitular.cep) }}</p>
            <p><strong>Endereço:</strong> {{ selectedTitular.endereco }}</p>
            <p><strong>Número:</strong> {{ selectedTitular.numero }}</p>
            <p>
              <strong>Complemento:</strong>
              {{ formatField(selectedTitular.complemento) }}
            </p>
            <p><strong>Bairro:</strong> {{ selectedTitular.bairro }}</p>
            <p><strong>Cidade:</strong> {{ selectedTitular.cidade }}</p>
            <p><strong>Estado:</strong> {{ selectedTitular.estado }}</p>
            <p>
              <strong>Número Contrato:</strong>
              {{ selectedTitular.numeroContrato }}
            </p>
            <p>
              <strong>Data Criação:</strong>
              {{ formatDate(selectedTitular.dataCriacao) }}
            </p>
            <p>
              <strong>Data Atualização:</strong>
              {{
                selectedTitular.dataCriacao === selectedTitular.dataAtualizacao
                  ? "-"
                  : formatDate(selectedTitular.dataAtualizacao)
              }}
            </p>
          </v-col>
        </v-row>
      </v-card-text>

      <!-- Dados dos Dependentes -->
      <v-divider></v-divider>
      <v-card-subtitle>Dependentes</v-card-subtitle>
      <v-card-text
        v-for="(dependente, index) in selectedTitular.dependentes"
        :key="index"
      >
        <v-row>
          <v-col cols="12" sm="6">
            <p><strong>Nome:</strong> {{ dependente.nome }}</p>
            <p>
              <strong>Nome Social:</strong>
              {{ formatField(dependente.nomeSocial) }}
            </p>
            <p><strong>CPF:</strong> {{ formatCPF(dependente.cpf) }}</p>
            <p><strong>Email:</strong> {{ dependente.email }}</p>
            <p>
              <strong>Data de Nascimento:</strong>
              {{ dependente.dataNascimento }}
            </p>
            <p>
              <strong>Telefone 1:</strong>
              {{ formatTelefone(dependente.telefone1) }}
            </p>
            <p>
              <strong>Telefone 2:</strong>
              {{ formatField(formatTelefone(dependente.telefone2)) }}
            </p>
            <p><strong>Parentesco:</strong> {{ dependente.parentesco }}</p>
          </v-col>
          <v-col cols="12" sm="6">
            <p><strong>CEP:</strong> {{ formatCEP(dependente.cep) }}</p>
            <p><strong>Endereço:</strong> {{ dependente.endereco }}</p>
            <p><strong>Número:</strong> {{ dependente.numero }}</p>
            <p>
              <strong>Complemento:</strong>
              {{ formatField(dependente.complemento) }}
            </p>
            <p><strong>Bairro:</strong> {{ dependente.bairro }}</p>
            <p><strong>Cidade:</strong> {{ dependente.cidade }}</p>
            <p><strong>Estado:</strong> {{ dependente.estado }}</p>
            <p>
              <strong>Data Criação:</strong>
              {{ formatDate(dependente.dataCriacao) }}
            </p>
            <p>
              <strong>Data Atualização:</strong>
              {{
                dependente.dataCriacao === dependente.dataAtualizacao
                  ? "-"
                  : formatDate(dependente.dataAtualizacao)
              }}
            </p>
          </v-col>
        </v-row>
        <v-divider></v-divider>
      </v-card-text>

      <v-card-actions class="d-flex justify-center">
        <v-btn text rounded="0" @click="closeDialog">Fechar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- Modal for Delete Confirmation -->
  <v-dialog v-model="blockDialog" max-width="500px">
    <v-card class="pa-1">
      <v-card-title class="headline">
        {{ blockedItem.acesso === "bloqueado" ? "Desbloquear" : "Bloquear" }}
      </v-card-title>
      <v-card-text>
        Você realmente deseja
        {{ blockedItem.acesso === "bloqueado" ? "desbloquear" : "bloquear" }}
        o usuário
        <strong>{{ blockedItem.nome }}</strong>
        ?
      </v-card-text>
      <v-card-actions class="action">
        <v-btn class="btnCancel" rounded="0" text @click="closeBlockDialog">
          Cancelar
        </v-btn>
        <v-btn
          class="btn-salvar"
          rounded="0"
          text
          @click="toggleBlock(blockedItem.id)"
        >
          {{ blockedItem.acesso === "bloqueado" ? "Desbloquear" : "Bloquear" }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

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
</template>

<script>
import axios from "axios";
import { format, subHours } from "date-fns";

export default {
  data() {
    return {
      search: "",
      dialog: false,
      blockDialog: false,
      hovered: false,
      selectedTitular: {},
      editedItem: {
        id: "",
        acesso: "",
        nome: "",
        nomeSocial: "",
        cpf: "",
        dataNascimento: "",
        email: "",
        telefone1: "",
        telefone2: "",
        numeroContrato: "",
        cep: "",
        endereco: "",
        numero: "",
        complemento: "",
        bairro: "",
        cidade: "",
        estado: "",
        status: "",
        dependentes: [
          {
            nome: "",
            nomeSocial: "",
            email: "",
            relacionamento: "",
            outroRelacionamento: "",
            cpf: "",
            dataNascimento: "",
            telefone1: "",
            telefone2: "",
            cep: "",
            endereco: "",
            numero: "",
            complemento: "",
            bairro: "",
            cidade: "",
            estado: "",
          },
        ],
      },
      headers: [
        { title: "Nome", align: "start", value: "nome", sortable: true },
        { title: "CPF", value: "cpf", sortable: true },
        {
          title: "Data Nascimento",
          value: "dataNascimento",
          sortable: true,
        },
        { title: "Email", value: "email", sortable: true },
        { title: "Telefone", value: "telefone1", sortable: true },
        { title: "Status", value: "status", sortable: true },
        { title: "", value: "actions", align: "center", sortable: false },
      ],
      texts: [],
      blockedItem: null,
      editedIndex: -1,
      defaultItem: {
        id: "",
        acesso: "",
        nome: "",
        nomeSocial: "",
        cpf: "",
        dataNascimento: "",
        email: "",
        telefone1: "",
        telefone2: "",
        numeroContrato: "",
        cep: "",
        endereco: "",
        numero: "",
        complemento: "",
        bairro: "",
        cidade: "",
        estado: "",
        status: "",
        dataCriacao: "",
        dataAtualizacao: "",
        dependentes: [
          {
            nome: "",
            nomeSocial: "",
            email: "",
            relacionamento: "",
            outroRelacionamento: "",
            cpf: "",
            dataNascimento: "",
            telefone1: "",
            telefone2: "",
            cep: "",
            endereco: "",
            numero: "",
            complemento: "",
            bairro: "",
            cidade: "",
            estado: "",
            dataCriacao: "",
            dataAtualizacao: "",
          },
        ],
      },
      snackbar: false,
      snackbarText: "",
      snackbarColor: "",
      snackbarIcon: "",
    };
  },
  watch: {
    showModalEdit(val) {
      this.dialog = val;
    },
    dialog(val) {
      if (!val) {
        this.$emit("close");
      }
    },
  },
  methods: {
    formatField(value) {
      return value === "" || value === null ? "-" : value;
    },
    formatCPF(cpf) {
      // Remove qualquer caractere não numérico
      cpf = cpf.replace(/\D/g, "");

      // Aplica a máscara no CPF (###.###.###-##)
      if (cpf.length === 11) {
        return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
      }

      return cpf; // Retorna o CPF sem formatação se o tamanho for inválido
    },
    formatCEP(cep) {
      // Remove qualquer caractere não numérico
      cep = cep.replace(/\D/g, "");

      // Aplica a máscara no CEP (#####-###)
      if (cep.length === 8) {
        return cep.replace(/(\d{5})(\d{3})/, "$1-$2");
      }

      return cep; // Retorna o CEP sem formatação se o tamanho for inválido
    },
    formatTelefone(telefone) {
      // Remove qualquer caractere não numérico
      telefone = telefone.replace(/\D/g, "");

      // Aplica a máscara no telefone (##) #####-####
      if (telefone.length === 11) {
        return telefone.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3");
      }

      return telefone; // Retorna o telefone sem formatação se o tamanho for inválido
    },
    formatDate(date) {
      if (!date) return "";
      // Subtrai 3 horas da data
      const adjustedDate = subHours(new Date(date), 3);
      // Formata a data no formato dd/MM/yyyy HH:mm
      return format(adjustedDate, "dd/MM/yyyy HH:mm");
    },
    capitalize(text) {
      if (!text) return "";
      return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
    },
    openAddModal() {
      this.dialogTitle = "Adicionar Texto";
      this.editedItem = Object.assign({}, this.defaultItem);
      this.dialog = true;
    },
    openViewModal(item) {
      this.dialogTitle = "Visualizar Usuário";
      this.editedItem = Object.assign({}, item);
      this.selectedTitular = item; // Armazena o titular e dependentes selecionados
      this.dialog = true; // Abre o modal
    },
    openBlockDialog(item) {
      this.blockedItem = item;
      this.blockDialog = true;
    },
    closeDialog() {
      this.dialog = false;
      setTimeout(() => {
        this.selectedTitular = {};
      }, 200);
      this.fetchUsers();
    },
    closeBlockDialog() {
      this.blockDialog = false;
    },
    saveItem() {
      this.dialog = false;
    },
    async fetchUsers() {
      try {
        const response = await axios.get("http://localhost:8181/admin.php");
        const items = response.data;

        // Processa os dados do titular para adequá-los ao formato da tabela
        this.texts = items.map((item) => ({
          id: item.titular.id,
          acesso: item.titular.acesso,
          nome: item.titular.nome,
          nomeSocial: item.titular.nomeSocial,
          cpf: item.titular.cpf,
          dataNascimento: item.titular.dataNascimento,
          email: item.titular.email,
          telefone1: item.titular.telefone1,
          telefone2: item.titular.telefone2,
          numeroContrato: item.titular.numeroContrato,
          cep: item.titular.cep,
          endereco: item.titular.endereco,
          numero: item.titular.numero,
          complemento: item.titular.complemento,
          bairro: item.titular.bairro,
          cidade: item.titular.cidade,
          estado: item.titular.estado,
          status: item.titular.status,
          dataCriacao: item.titular.dataCriacao,
          dataAtualizacao: item.titular.dataAtualizacao,
          dependentes: item.dependentes,
        }));
      } catch (error) {
        console.error("Erro ao buscar usuários:", error);
      }
    },
    toggleBlock(id) {
      // Fazer a requisição PATCH com o ID do titular
      axios
        .patch("http://localhost:8181/admin.php", {
          id: id,
        })
        .then((response) => {
          if (response.data.status == "success") {
            this.snackbarText = response.data.message;
            this.snackbarColor = "success";
            this.snackbarIcon = "mdi-check-circle";
            this.snackbar = true;
            this.closeBlockDialog();
            this.fetchUsers();
          } else {
            this.snackbarText = response.data.message;
            this.snackbarColor = "error";
            this.snackbarIcon = "mdi-alert-circle";
            this.snackbar = true;
            this.closeBlockDialog();
            this.fetchUsers();
          }
        })
        .catch((error) => {
          this.snackbarText = "Erro ao atualizar acesso do titular.";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
          this.fetchUsers();
        });
    },
  },
  mounted() {
    this.fetchUsers();
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

.btn-salvar {
  background-color: #91c141 !important;
  color: white !important;
}

.btn-salvar:hover {
  background-color: #6d9232 !important;
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

.status-ativo {
  font-weight: 600;
  color: #00af49;
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

@media screen and (min-width: 1200px){
  .cardWrapper{
    max-width: 1200px;
    margin: 0 auto;
  }
}

</style>
