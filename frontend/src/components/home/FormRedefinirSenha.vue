<template>
  <v-container>
    <div class="container" style="height: auto">
      <v-card
        elevation="5"
        v-show="mostrarCpf"
        class="pa-2"
        style="margin: 0 auto"
        max-width="400px"
      >
        <v-card-title>Redefinir Senha</v-card-title>
        <v-form @submit.prevent="submitCpf()" ref="formCpf">
          <v-card-text class="pb-0">
            <div class="pb-3 text-center">
              <p>Informe o seu CPF para recuperar sua senha.</p>
            </div>
            <v-row>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  label="CPF"
                  type="text"
                  v-model="cpfSenha"
                  v-mask="'###.###.###-##'"
                  required
                  :rules="[rules.required, rules.cpf, rules.emoji]"
                  maxlength="14"
                  counter
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="cardActions pt-3">
            <v-row class="d-flex justify-center" dense>
              <v-col cols="auto">
                <v-btn
                  class="btn-submit"
                  rounded="0"
                  text
                  type="submit"
                  block
                  :loading="loading"
                >
                  Enviar código
                </v-btn>
              </v-col>
            </v-row>
          </v-card-actions>
        </v-form>
      </v-card>

      <!-- código enviado -->
      <v-card
        elevation="5"
        v-show="inserirCodigo"
        class="pa-2"
        style="margin: 0 auto"
        max-width="400px"
      >
        <v-card-title>Redefinir Senha</v-card-title>
        <v-form @submit.prevent="submitCodigo()" ref="formCodigo">
          <v-card-text class="pb-0">
            <div class="pb-3 text-center">
              <p>
                Insira o código de 6 dígitos enviado para o e-mail:<br />
                <strong style="font-size: 18px">{{
                  censurarEmail(email)
                }}</strong>
              </p>
            </div>
            <v-row>
              <v-col cols="12" class="pb-0">
                <v-otp-input variant="solo-filled" v-model="codigo" required>
                </v-otp-input>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="cardActions pt-3">
            <v-row class="d-flex justify-center" dense>
              <v-col cols="12" class="text-center">
                <a
                  href="javascript:void(0)"
                  @click="reenviarCodigo"
                  style="
                    font-size: 14px;
                    cursor: pointer;
                    text-decoration: none;
                    color: #1976d2;
                  "
                  >Reenviar código</a
                >
              </v-col>
              <v-col cols="12">
                <v-btn class="btn-submit" rounded="0" text type="submit" block :loading="loading">
                  Confirmar código
                </v-btn>
              </v-col>
            </v-row>
          </v-card-actions>
        </v-form>
      </v-card>

      <!-- código confirmado -->
      <v-card
        elevation="5"
        v-show="alterarSenha"
        class="pa-2"
        style="margin: 0 auto"
        max-width="400px"
      >
        <v-card-title>Redefinir Senha</v-card-title>
        <v-form @submit.prevent="submitSenha()" ref="formSenha">
          <v-card-text class="pb-0">
            <div class="pb-3 text-center">
              <p>Digite a sua nova senha.</p>
            </div>
            <v-row>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  label="Senha"
                  required
                  :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                  :type="visible ? 'text' : 'password'"
                  v-model="senha"
                  :rules="[rules.required, rules.senhaTamanho, rules.emoji]"
                  @click:append-inner="visible = !visible"
                  maxlength="32"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  label="Confirmação da senha"
                  required
                  :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
                  :type="visible ? 'text' : 'password'"
                  v-model="senhaNovamente"
                  :rules="[
                    rules.required,
                    rules.senhaIgual,
                    rules.senhaTamanho,
                    rules.emoji,
                  ]"
                  @click:append-inner="visible = !visible"
                  maxlength="32"
                  counter
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="cardActions pt-3">
            <v-row class="d-flex justify-center" dense>
              <v-col cols="auto">
                <v-btn class="btn-submit" rounded="0" text type="submit" block>
                  Salvar
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
import Swal from "sweetalert2";
import router from "@/router";

export default {
  data() {
    return {
      visible: false,
      mostrarCpf: true,
      inserirCodigo: false,
      alterarSenha: false,
      loading: false,
      cpfSenha: "",
      codigo: "",
      email: "",
      senha: "",
      senhaNovamente: "",
      snackbar: false,
      snackbarText: "",
      snackbarColor: "",
      snackbarIcon: "",
      rules: {
        required: (value) => !!value || "Este campo é obrigatório.",
        cpf: (value) => this.validateCPF(value) || "CPF inválido.",
        emoji: (value) =>
          !/(?:\p{Extended_Pictographic})/u.test(value) ||
          "Caractere inválido.",
        senhaIgual: (value) =>
          value === this.senha || "As senhas não coincidem.",
        senhaTamanho: (value) =>
          (value.length >= 6 && value.length <= 32) ||
          "Sua senha deve ter pelo menos 6 caracteres e no máximo 32.",
      },
    };
  },
  methods: {
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
    async submitCpf() {
      const isValid = await this.$refs.formCpf.validate();

      if (!isValid.valid) {
        return;
      }

      this.loading = true;

      try {
        const formData = {
          cpf: this.cpfSenha.replace(/\D/g, ""),
        };

        await axios
          .patch("http://localhost:8181/redefinirSenha.php?acao=cpf", formData)
          .then((response) => {
            console.log("Resposta do servidor: ", response.data);
            if (response.data.status == "error") {
              this.snackbarText = response.data.message;
              this.snackbarColor = "error";
              this.snackbarIcon = "mdi-alert-circle";
              this.snackbar = true;
            } else {
              this.email = response.data.email;
              this.mostrarCpf = false;
              this.inserirCodigo = true;
              this.alterarSenha = false;
            }
          });
      } catch (error) {
        this.snackbarText = "Erro ao enviar informações.";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
      } finally {
        this.loading = false;
      }
    },
    async submitCodigo() {
      if (this.codigo.length != 6) {
        this.snackbarText = "Código inválido.";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
        return;
      }

      const formData = {
        codigo: this.codigo,
        cpf: this.cpfSenha.replace(/\D/g, ""),
      };

      const response = await axios.post(
        "http://localhost:8181/redefinirSenha.php?acao=codigo",
        formData
      );

      if (response.data.status == "error") {
        this.snackbarText = response.data.message;
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
        return;
      }

      this.mostrarCpf = false;
      this.inserirCodigo = false;
      this.alterarSenha = true;
    },
    async reenviarCodigo() {
      this.loading = true;

      try {
        const formData = {
          cpf: this.cpfSenha.replace(/\D/g, ""),
        };

        await axios
          .patch("http://localhost:8181/redefinirSenha.php?acao=cpf", formData)
          .then((response) => {
            if (response.data.status == "error") {
              this.snackbarText = response.data.message;
              this.snackbarColor = "error";
              this.snackbarIcon = "mdi-alert-circle";
              this.snackbar = true;
            } else {
              this.snackbarText = response.data.message;
              this.snackbarColor = "success";
              this.snackbarIcon = "mdi-check-circle";
              this.snackbar = true;
            }
          });
      } catch (error) {
        this.snackbarText = "Erro ao reenviar código.";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
      } finally {
        this.loading = false;
      }
    },
    async submitSenha() {
      const isValid = await this.$refs.formSenha.validate();

      if (!isValid.valid) {
        return;
      }

      try {
        const formData = {
          cpf: this.cpfSenha.replace(/\D/g, ""),
          senha: this.senha,
        };
        await axios
          .patch(
            "http://localhost:8181/redefinirSenha.php?acao=senha",
            formData
          )
          .then((response) => {
            if (response.data.status == "error") {
              this.snackbarText = response.data.message;
              this.snackbarColor = "error";
              this.snackbarIcon = "mdi-alert-circle";
              this.snackbar = true;
              return;
            }

            Swal.fire({
              title: "Senha redefinida com sucesso!",
              icon: "success",
              text: "Redirecionando para a página inicial.",
              confirmButtonColor: "rgba(145, 193, 65, 0.6)",
              timer: 3500,
              timerProgressBar: true,
            }).then(() => {
              router.push({ path: "/" });
            });
          });
      } catch (error) {
        this.snackbarText = "Erro ao redefinir senha.";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
      }
    },
    censurarEmail(email) {
      if (!email.includes("@")) return email; // Verifica se o e-mail é válido
      const [nome, dominio] = email.split("@");
      const visivel = nome.slice(0, 4); // Até as 4 primeiras letras ou o nome inteiro, caso tenha menos
      const restante = nome.length > 4 ? "*".repeat(nome.length - 4) : ""; // Adiciona asteriscos apenas se necessário
      const censurado = visivel + restante + "@" + dominio;
      return censurado;
    },
  },
};
</script>

<style scoped>
.cardActions {
  display: flex;
  justify-content: center;
}

.btn-submit {
  background-color: #91c141 !important;
  color: white !important;
}

.btn-submit:hover {
  background-color: #6d9232 !important;
}
</style>
