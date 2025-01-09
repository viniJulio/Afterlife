<template>
  <v-dialog v-model="dialog" max-width="400" @click:outside="closeDialog">
    <v-card class="pa-4">
      <v-card-title class="text-center">Acesse sua Conta</v-card-title>
      <v-form
        v-model="form"
        @submit.prevent="submitForm()"
        @keydown.enter.prevent
        ref="form"
      >
        <v-card-text class="pb-0">
          <v-text-field
            label="CPF"
            type="text"
            required
            v-model="cpf"
            v-mask="'###.###.###-##'"
            :rules="[rules.required, rules.cpf]"
            maxlength="14"
            counter
          ></v-text-field>
          <v-text-field
            label="Senha"
            required
            :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
            :type="visible ? 'text' : 'password'"
            v-model="password"
            :rules="[rules.required, rules.emoji]"
            @click:append-inner="visible = !visible"
            maxlength="32"
            counter
          ></v-text-field>
        </v-card-text>
        <v-card-actions class="pb-1">
          <v-btn
            class="btn-entrar"
            rounded="0"
            text
            type="submit"
            block
            :loading="loading"
            >Entrar</v-btn
          >
        </v-card-actions>
      </v-form>
      <div class="text-center">
        <a
          href="javascript:void(0)"
          style="
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            color: #1976d2;
            font-weight: 500;
          "
          @click.prevent="createAccount"
          >Crie sua conta</a
        >
        <span> | </span>
        <a
          class="esqueceuSenha"
          href="javascript:void(0)"
          @click="goToRedefinirSenha"
          style="font-size: 14px"
          >Esqueceu sua senha?</a
        >
      </div>
    </v-card>
  </v-dialog>
  <v-snackbar
    v-model="snackbar"
    timeout="4500"
    elevation="24"
    :color="snackbarColor"
  >
    <v-icon left class="pe-2">{{ snackbarIcon }}</v-icon>
    <span v-html="snackbarText"></span>
    <!-- Usando v-html para renderizar HTML -->
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
  name: "ModalLogin",
  emits: ["close", "open-register"],
  props: {
    showModal: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      form: false,
      dialog: this.showModal,
      visible: false,
      cpf: "",
      password: "",
      rules: {
        required: (value) => !!value || "Este campo é obrigatório.",
        cpf: (value) => this.validateCPF(value) || "CPF inválido.",
        emoji: (value) =>
          !/(?:\p{Extended_Pictographic})/u.test(value) ||
          "Caractere inválido.",
      },
      snackbar: false,
      snackbarText: "",
      snackbarColor: "",
      loading: false,
    };
  },
  watch: {
    showModal(val) {
      this.dialog = val;
    },
    dialog(val) {
      if (!val) {
        this.$emit("close");
        this.resetFields();
      }
    },
  },
  methods: {
    goToRedefinirSenha() {
      this.$router.push("redefinirSenha");
    },
    createAccount() {
      this.$emit("close"); // Fecha o modal de login
      this.$emit("open-register"); // Abre o modal de registro
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
      this.dialog = false;
    },
    async submitForm() {
      const isValid = await this.$refs.form.validate();

      if (!isValid.valid) {
        return;
      }

      const formData = {
        cpf: this.cpf.replace(/\D/g, ""),
        senha: this.password,
      };

      await axios
        .post("http://localhost:8181/login.php", formData, {
          withCredentials: true,
        })
        .then((response) => {
          if (response.data.status == "error") {
            this.snackbarText = response.data.message;
            this.snackbarColor = "error";
            this.snackbarIcon = "mdi-alert-circle";
            this.snackbar = true;
          } else if (response.data.status == "success") {
            this.$router.push("pastas");
          }
        })
        .catch((error) => {
          console.error("Erro na requisição: ", error);
        });
    },
    resetFields() {
      this.cpf = "";
      this.password = "";
      this.visible = false;
      this.loading = false;
    },
  },
};
</script>

<style scoped>
.btn-entrar {
  background-color: #91c141 !important;
  color: white !important;
}
.btn-entrar:hover {
  background-color: #6d9232 !important;
}
.esqueceuSenha {
  text-decoration: none;
  color: black;
  font-size: 14px;
}
</style>
