<template>
  <v-dialog v-model="dialog" max-width="700px">
    <v-card class="pa-2">
      <v-card-title>Editar Dados</v-card-title>
      <v-form @submit.prevent="submitForm()" ref="formConfiguracao">
        <v-card-text class="pb-0">
          <v-row>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Nome completo"
                type="text"
                v-model="nome"
                required
                :rules="[rules.required, rules.nome, rules.emoji]"
                maxlength="120"
                counter
              >
              </v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Nome social"
                type="text"
                v-model="nomeSocial"
                :rules="[rules.nomeSocial, rules.emoji]"
                maxlength="120"
                counter
              >
              </v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="CPF"
                type="text"
                v-model="cpf"
                required
                :rules="[rules.required, rules.cpf, rules.emoji]"
                v-mask="'###.###.###-##'"
                maxlength="14"
                counter
                disabled
              >
              </v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="E-mail"
                type="email"
                required
                v-model="email"
                :rules="[rules.required, rules.email, rules.emoji]"
                maxlength="80"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Telefone"
                type="text"
                required
                v-mask="'(##) #####-####'"
                v-model="telefone"
                :rules="[rules.required, rules.telefone, rules.emoji]"
                maxlength="15"
                counter
              ></v-text-field>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="d-flex justify-center">
          <v-btn text rounded="0" @click="closeDialog">Cancelar</v-btn>
          <v-btn class="btn-entrar" type="submit" rounded="0" text
            >Salvar</v-btn
          >
        </v-card-actions>
      </v-form>
    </v-card>
  </v-dialog>
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

export default {
  name: "ModalEditAdmin",
  emits: ["close"],
  props: {
    showEditAdmin: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      dialog: this.showEditAdmin,
      nome: "",
      nomeSocial: "",
      senhaAtual: "",
      senhaNova: "",
      senhaNovamente: "",
      cpf: "",
      email: "",
      telefone: "",
      baseUrl: "http://localhost:8181/",
      snackbar: false,
      snackbarText: "",
      snackbarColor: "",
      snackbarIcon: "",
      rules: {
        required: (value) => !!value || "Este campo é obrigatório.",
        nome: (value) =>
          (/^[A-Za-zÀ-ÿ]{2,}( [A-Za-zÀ-ÿ]{2,})+$/.test(value) &&
            value.length <= 120) ||
          "Digite pelo menos um nome e um sobrenome.",
        nomeSocial: (value) =>
          value.length <= 120 || "Nome social muito longo.",
        cpf: (value) => this.validateCPF(value) || "CPF inválido.",
        telefone: (value) => this.validateTelefone(value),
        email: (value) =>
          (/.+@.+\..+/.test(value) && value.length <= 80) || "E-mail inválido.",
        senhaIgual: (value) =>
          value === this.senhaNova || "As senhas não coincidem.",
        senhaTamanho: (value) =>
          (value.length >= 6 && value.length <= 32) ||
          "Sua senha deve ter pelo menos 6 caracteres e no máximo 32.",
        emoji: (value) =>
          !/(?:\p{Extended_Pictographic})/u.test(value) ||
          "Caractere inválido.",
      },
    };
  },
  watch: {
    showEditAdmin(val) {
      this.dialog = val; // Atualiza o `dialog` local quando a prop mudar
    },
    dialog(val) {
      if (!val) {
        this.$emit("close"); // Emite o evento ao fechar o modal
      } else {
        this.fetchDados();
      }
    },
  },
  methods: {
    validateTelefone(value) {
      if (!value) return true;

      // Verifica o formato do telefone
      const telefoneRegex = /^\(\d{2}\) 9\d{4}-\d{4}$/;
      if (!telefoneRegex.test(value)) {
        return "Telefone inválido.";
      }

      if (value.length > 15) {
        return "Telefone inválido.";
      }

      return true;
    },
    validateCPF(cpf) {
      // Remove qualquer coisa que não seja número
      cpf = cpf.replace(/[^\d]/g, "");

      // Verifica se o CPF tem 11 dígitos
      if (cpf.length !== 11) return false;

      // Verifica se o CPF é um número repetido (ex.: 111.111.111-11)
      if (/^(\d)\1{10}$/.test(cpf)) return false;

      // Valida os dois últimos dígitos verificadores
      let sum = 0;
      let remainder;

      // Validação do primeiro dígito verificador
      for (let i = 0; i < 9; i++) {
        sum += parseInt(cpf.charAt(i)) * (10 - i);
      }
      remainder = (sum * 10) % 11;
      if (remainder === 10 || remainder === 11) remainder = 0;
      if (remainder !== parseInt(cpf.charAt(9))) return false;

      sum = 0;
      // Validação do segundo dígito verificador
      for (let i = 0; i < 10; i++) {
        sum += parseInt(cpf.charAt(i)) * (11 - i);
      }
      remainder = (sum * 10) % 11;
      if (remainder === 10 || remainder === 11) remainder = 0;
      if (remainder !== parseInt(cpf.charAt(10))) return false;

      return true;
    },
    closeDialog() {
      this.dialog = false; // Fecha o modal
    },
    async fetchDados() {
      try {
        const response = await axios.get(
          this.baseUrl + "admin.php?acao=dadosAdmin",
          { withCredentials: true }
        );

        const dados = response.data[0];

        this.nome = dados.nome;
        this.nomeSocial = dados.nomeSocial;
        this.cpf = dados.cpf;
        this.email = dados.email;
        this.telefone = dados.telefone;
      } catch (error) {
        console.log(error);
      }
    },
    async submitForm() {
      const isValid = await this.$refs.formConfiguracao.validate();

      if (!isValid.valid) {
        return;
      }

      const formData = {
        nome: this.nome,
        nomeSocial: this.nomeSocial,
        cpf: this.cpf,
        email: this.email,
        telefone: this.telefone,
      };

      axios
        .patch(this.baseUrl + "admin.php?acao=editAdmin", formData, {
          withCredentials: true,
        })
        .then((response) => {
          console.log(response.data);
          if (response.data.status == "error") {
            console.log(response.data);
            this.snackbarText = "Erro ao alterar dados";
            this.snackbarColor = "error";
            this.snackbarIcon = "mdi-alert-circle";
            this.snackbar = true;
            return;
          }

          console.log("Resposta do servidor: ", response.data);
          this.closeDialog();
          this.snackbarText = "Dados alterados com sucesso";
          this.snackbarColor = "success";
          this.snackbarIcon = "mdi-check-circle";
          this.snackbar = true;
        })
        .catch((error) => {
          console.error("Erro ao enviar o formulário:", error);
          this.disabledButton = false;
          this.snackbarText = "Erro ao alterar dados";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
        });
    },
  },
  mounted() {
    this.fetchDados();
  },
  created() {
    this.fetchDados();
  },
};
</script>

<style scoped>
.btn-entrar {
  background-color: #91c141 !important;
  color: white !important;
  width: 100px;
}

.btn-entrar:hover {
  background-color: #6d9232 !important;
}

.action {
  display: flex;
  justify-content: center;
}
</style>
