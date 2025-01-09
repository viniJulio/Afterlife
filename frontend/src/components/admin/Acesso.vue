<template>
  <v-container>
    <div class="container" style="height: auto">
      <v-card
        elevation="5"
        v-show="mostrarCadastro"
        class="pa-2"
        style="margin: 0 auto"
        max-width="1000px"
      >
        <v-card-title>Admin - Cadastro</v-card-title>
        <v-form @submit.prevent="submitCadastro()" ref="formCadastro">
          <v-card-text class="pb-0">
            <v-row>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Nome completo"
                  type="text"
                  v-model="nome"
                  autofocus
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
                  v-mask="'###.###.###-##'"
                  required
                  :rules="[rules.required, rules.cpf, rules.emoji]"
                  maxlength="14"
                  counter
                >
                </v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="E-mail"
                  type="email"
                  v-model="email"
                  required
                  :rules="[rules.required, rules.email, rules.emoji]"
                  maxlength="80"
                  counter
                >
                </v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Telefone"
                  type="text"
                  v-model="telefone"
                  v-mask="'(##) #####-####'"
                  required
                  :rules="[rules.required, rules.telefone, rules.emoji]"
                  maxlength="15"
                  counter
                >
                </v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Senha"
                  v-model="senha"
                  :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                  :type="visible ? 'text' : 'password'"
                  @click:append-inner="visible = !visible"
                  required
                  :rules="[rules.required, rules.senhaTamanho, rules.emoji]"
                  maxlength="32"
                  counter
                >
                </v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  label="Confirmação da senha"
                  v-model="senhaNovamente"
                  :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                  :type="visible ? 'text' : 'password'"
                  @click:append-inner="visible = !visible"
                  required
                  :rules="[
                    rules.required,
                    rules.senhaIgual,
                    rules.senhaTamanho,
                    rules.emoji,
                  ]"
                  maxlength="32"
                  counter
                >
                </v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="cardActions">
            <v-row class="d-flex justify-center" dense>
              <v-col cols="12" sm="auto">
                <v-btn rounded="0" text type="button" @click="changeForm" block>
                  Já possuo uma conta
                </v-btn>
              </v-col>
              <v-col cols="12" sm="auto">
                <v-btn
                  class="btn-entrar"
                  rounded="0"
                  text
                  type="submit"
                  block
                  :loading="loading"
                >
                  Salvar
                </v-btn>
              </v-col>
            </v-row>
          </v-card-actions>
        </v-form>
      </v-card>

      <v-card
        elevation="5"
        v-show="mostrarLogin"
        class="pa-2"
        style="margin: 0 auto"
        max-width="400px"
      >
        <v-card-title>Admin - Login</v-card-title>
        <v-form @submit.prevent="submitLogin()" ref="formLogin">
          <v-card-text class="pb-0">
            <v-row>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  label="CPF"
                  type="text"
                  v-model="cpfLogin"
                  v-mask="'###.###.###-##'"
                  required
                  :rules="[rules.required, rules.cpf, rules.emoji]"
                  maxlength="14"
                  counter
                >
                </v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  label="Senha"
                  v-model="senhaLogin"
                  :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                  :type="visible ? 'text' : 'password'"
                  @click:append-inner="visible = !visible"
                  required
                  :rules="[rules.required, rules.emoji]"
                  maxlength="32"
                  counter
                >
                </v-text-field>
              </v-col>
            </v-row>
          </v-card-text>

          <v-card-actions class="cardActions">
            <v-row class="d-flex justify-center" dense>
              <v-col cols="12" sm="auto">
                <v-btn rounded="0" text type="button" @click="changeForm" block>
                  Não possuo uma conta
                </v-btn>
              </v-col>
              <v-col cols="12" sm="auto">
                <v-btn class="btn-entrar" rounded="0" text type="submit" block>
                  Entrar
                </v-btn>
              </v-col>
            </v-row>
          </v-card-actions>
        </v-form>
      </v-card>
    </div>
  </v-container>

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

export default {
  data() {
    return {
      visible: false,
      mostrarLogin: true,
      mostrarCadastro: false,
      nome: "",
      nomeSocial: "",
      senha: "",
      senhaNovamente: "",
      cpf: "",
      email: "",
      telefone: "",
      cpfLogin: "",
      senhaLogin: "",
      loading: false,
      rules: {
        required: (value) => !!value || "Este campo é obrigatório.",
        nome: (value) =>
          (/^[A-Za-zÀ-ÿ]{2,}( [A-Za-zÀ-ÿ]{2,})+$/.test(value) &&
            value.length <= 120) ||
          "Digite pelo menos um nome e um sobrenome.",
        nomeSocial: (value) =>
          value.length <= 120 || "Nome social muito longo.",
        cpf: (value) => this.validateCPF(value) || "CPF inválido.",
        email: (value) =>
          (/.+@.+\..+/.test(value) && value.length <= 80) || "E-mail inválido.",
        senhaIgual: (value) =>
          value === this.senha || "As senhas não coincidem.",
        senhaTamanho: (value) =>
          (value.length >= 6 && value.length <= 32) ||
          "Sua senha deve ter pelo menos 6 caracteres e no máximo 32.",
        telefone: (value) => this.validateTelefone(value),
        emoji: (value) =>
          !/(?:\p{Extended_Pictographic})/u.test(value) ||
          "Caractere inválido.",
      },
      snackbar: false,
      snackbarText: "",
      snackbarColor: "",
    };
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
    changeForm() {
      if (this.mostrarLogin == true) {
        this.mostrarLogin = false;
        this.mostrarCadastro = true;
      } else {
        this.mostrarLogin = true;
        this.mostrarCadastro = false;
      }
    },
    async submitLogin() {
      const isValid = await this.$refs.formLogin.validate();

      if (!isValid.valid) {
        return;
      }

      const formData = {
        cpf: this.cpfLogin.replace(/\D/g, ""),
        senha: this.senhaLogin,
      };

      console.log(formData);

      axios
        .post("http://localhost:8181/login.php?acao=admin", formData, {
          withCredentials: true,
        })
        .then((response) => {
          if (response.data.status == "error") {
            this.snackbarText = "Admin não autenticado";
            this.snackbarColor = "error";
            this.snackbarIcon = "mdi-alert-circle";
            this.snackbar = true;
          } else if (response.data.status == "success") {
            this.$router.push("admin");
          }
        })
        .catch((error) => {
          console.log("Erro na requisição: ", error);
        });
    },
    async submitCadastro() {
      const isValid = await this.$refs.formCadastro.validate();

      if (!isValid.valid) {
        return;
      }

      this.loading = true;

      const formData = {
        nome: this.nome,
        nomeSocial: this.nomeSocial,
        cpf: this.cpf,
        senha: this.senha,
        telefone: this.telefone,
        email: this.email,
      };

      console.log(formData);

      axios
        .post("http://localhost:8181/cadastro.php?acao=admin", formData)
        .then((response) => {
          if (response.data.status == "success") {
            const token = response.data.token;
            axios
              .post(
                "http://localhost:8181/emailAdmin.php?acao=solicitarCadastro&token=" +
                  token
              )
              .then((response) => {
                if (response.status == 200) {
                  this.snackbarText =
                    "Solicitação de cadastro enviada com sucesso";
                  this.snackbarColor = "success";
                  this.snackbarIcon = "mdi-check-circle";
                  this.snackbar = true;
                } else {
                  console.log("Não foi possível enviar o e-mail.");
                  console.log(response);
                }
              });
          } else {
            console.log("falha ao enviar o formulário");
            this.disabledButton = false;
            this.snackbarText = response.data.message;
            this.snackbarColor = "error";
            this.snackbarIcon = "mdi-alert-circle";
            this.snackbar = true;
          }
        })
        .catch((error) => {
          console.error("Erro ao enviar o formulário:", error);
          this.disabledButton = false;
          this.snackbarText = "Erro ao salvar cadastro";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
        });
      this.loading = false;
    },
  },
};
</script>

<style scoped>
.cardActions {
  display: flex;
  justify-content: center;
}

.btn-entrar {
  background-color: #91c141 !important;
  color: white !important;
  width: 100px;
}

.btn-entrar:hover {
  background-color: #6d9232 !important;
}
</style>
