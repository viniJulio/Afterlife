<template>
  <v-dialog v-model="dialog" max-width="1000px">
    <v-card class="pa-2">
      <v-card-title>
        Cadastro
        <span>|</span>
        <a
          href="javascript:void(0)"
          @click="openModalLogin"
          style="
            font-size: 12px;
            vertical-align: middle;
            cursor: pointer;
            text-decoration: none;
            color: #1976d2;
          "
        >
          Acesse sua conta
        </a>
      </v-card-title>
      <v-form @submit.prevent="submitForm()" ref="formCadastro">
        <v-card-text class="pb-0">
          <h4 class="pb-2 font-weight-bold">TITULAR</h4>
          <v-row>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Nome completo"
                type="text"
                v-model="nomeTitular"
                autofocus
                required
                :rules="[rules.required, rules.nome, rules.emoji]"
                maxlength="120"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Nome social"
                type="text"
                v-model="nomeSocial"
                :rules="[rules.nomeSocial, rules.emoji]"
                maxlength="120"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="CPF"
                type="text"
                required
                v-model="cpf"
                v-mask="'###.###.###-##'"
                :rules="[rules.required, rules.cpf, rules.emoji]"
                maxlength="14"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Data de nascimento"
                type="text"
                required
                v-model="dataNascimento"
                v-mask="'##/##/####'"
                :rules="[rules.required, rules.dataNascimento, rules.emoji]"
                maxlength="10"
                counter
              ></v-text-field>
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
                label="Telefone 1"
                type="text"
                required
                v-model="telefone1"
                v-mask="'(##) #####-####'"
                :rules="[rules.required, rules.telefone, rules.emoji]"
                maxlength="15"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Telefone 2"
                type="text"
                v-model="telefone2"
                v-mask="'(##) #####-####'"
                :rules="[rules.telefone, rules.emoji]"
                maxlength="15"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Número de contrato"
                type="text"
                v-mask="'############'"
                v-model="numeroContrato"
                :rules="[rules.numeroContrato, rules.required, rules.emoji]"
                required
                maxlength="12"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="CEP"
                type="text"
                required
                v-model="cep"
                v-mask="'#####-###'"
                :rules="[rules.required, rules.cep, rules.emoji]"
                maxlength="9"
                counter
                @input="fillAddress(cep)"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Endereço"
                type="text"
                required
                v-model="endereco"
                :rules="[rules.required, rules.endereco, rules.emoji]"
                maxlength="60"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Número"
                type="text"
                required
                v-model="numero"
                :rules="[rules.required, rules.numero, rules.emoji]"
                maxlength="10"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Complemento"
                type="text"
                v-model="complemento"
                :rules="[rules.emoji]"
                maxlength="80"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Bairro"
                type="text"
                required
                v-model="bairro"
                :rules="[rules.required, rules.bairro, rules.emoji]"
                maxlength="60"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Cidade"
                type="text"
                required
                v-model="cidade"
                :rules="[rules.required, rules.cidade, rules.emoji]"
                maxlength="60"
                counter
                disabled
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                v-model="estado"
                label="Estado"
                type="text"
                required
                :rules="[rules.required, rules.emoji, rules.estado]"
                maxlength="2"
                counter
                disabled
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
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
            <v-col cols="12" md="6">
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
          <h4 class="pt-2 font-weight-bold">DEPENDENTES</h4>
          <div v-for="(dependente, index) in dependentes" :key="index">
            <div class="d-flex justify-space-between pt-1">
              <h4 class="pt-4 pb-2">DEPENDENTE {{ index + 1 }}</h4>
              <v-tooltip
                text="Excluir"
                location="bottom"
                v-if="dependentes.length > 1"
              >
                <template v-slot:activator="{ props }">
                  <v-btn
                    v-bind="props"
                    style="color: #ef5350"
                    icon="mdi-delete"
                    elevation="0"
                    @click="removeDependent(index)"
                  ></v-btn>
                </template>
              </v-tooltip>
            </div>
            <v-row>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Nome completo"
                  type="text"
                  v-model="dependente.nome"
                  required
                  :rules="[rules.required, rules.nome, rules.emoji]"
                  maxlength="120"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Nome social"
                  type="text"
                  v-model="dependente.nomeSocial"
                  required
                  :rules="[rules.nomeSocial, rules.emoji]"
                  maxlength="120"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="E-mail"
                  type="email"
                  v-model="dependente.email"
                  required
                  :rules="[
                    rules.required,
                    rules.email,
                    rules.emoji,
                    rules.emailUnicoDependentes,
                    rules.emailTitularDiferenteDependente,
                  ]"
                  maxlength="80"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-select
                  label="Grau de Parentesco"
                  :items="relationship"
                  v-model="dependente.relacionamento"
                  required
                  :rules="[rules.required, rules.emoji]"
                ></v-select>
              </v-col>
              <v-col
                cols="12"
                md="6"
                class="pb-0"
                v-if="dependente.relacionamento === 'Outros'"
              >
                <v-text-field
                  label="Especifique o grau de parentesco"
                  type="text"
                  v-model="dependente.outroRelacionamento"
                  :rules="[rules.required, rules.parentesco, rules.emoji]"
                  maxlength="30"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="CPF"
                  type="text"
                  v-model="dependente.cpf"
                  required
                  v-mask="'###.###.###-##'"
                  :rules="[
                    rules.required,
                    rules.cpf,
                    rules.emoji,
                    rules.cpfUnicoDependentes,
                    rules.cpfTitularDiferenteDependente,
                  ]"
                  maxlength="14"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Data de nascimento"
                  type="text"
                  required
                  v-model="dependente.dataNascimento"
                  v-mask="'##/##/####'"
                  :rules="[rules.required, rules.dataNascimento, rules.emoji]"
                  maxlength="10"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Telefone 1"
                  type="text"
                  v-model="dependente.telefone1"
                  required
                  v-mask="'(##) #####-####'"
                  :rules="[rules.required, rules.telefone, rules.emoji]"
                  maxlength="15"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Telefone 2"
                  type="text"
                  v-model="dependente.telefone2"
                  v-mask="'(##) #####-####'"
                  :rules="[rules.telefone, rules.emoji]"
                  maxlength="15"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="CEP"
                  type="text"
                  required
                  v-model="dependente.cep"
                  v-mask="'#####-###'"
                  :rules="[rules.required, rules.cep, rules.emoji]"
                  maxlength="9"
                  counter
                  @input="fillAddressDependente(dependente.cep, index)"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Endereço"
                  type="text"
                  required
                  v-model="dependente.endereco"
                  :rules="[rules.required, rules.endereco, rules.emoji]"
                  maxlength="60"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Número"
                  type="number"
                  required
                  v-model="dependente.numero"
                  :rules="[rules.required, rules.numero, rules.emoji]"
                  maxlength="10"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Complemento"
                  type="text"
                  v-model="dependente.complemento"
                  :rules="[rules.emoji]"
                  maxlength="80"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Bairro"
                  type="text"
                  required
                  v-model="dependente.bairro"
                  :rules="[rules.required, rules.bairro, rules.emoji]"
                  maxlength="60"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6" class="pb-0">
                <v-text-field
                  label="Cidade"
                  type="text"
                  required
                  v-model="dependente.cidade"
                  :rules="[rules.required, rules.cidade, rules.emoji]"
                  maxlength="60"
                  counter
                  disabled
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="4" class="pb-0">
                <v-text-field
                  v-model="dependente.estado"
                  label="Estado"
                  type="text"
                  required
                  :rules="[rules.required, rules.emoji, rules.estado]"
                  maxlength="2"
                  counter
                  disabled
                ></v-text-field>
              </v-col>
            </v-row>
          </div>
          <v-col cols="12" class="text-center mb-2 mt-0">
            <v-btn text elevation="0" rounded="0" @click="addDependent"
              ><span class="mdi mdi-plus"></span>Adicionar</v-btn
            >
          </v-col>
        </v-card-text>
        <v-card-actions class="d-flex justify-center">
          <v-btn text rounded="0" @click="closeDialog">Cancelar</v-btn>
          <v-btn class="btn-entrar" rounded="0" text type="submit"
            >Salvar</v-btn
          >
        </v-card-actions>
      </v-form>
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

export default {
  name: "ModalRegister",
  emits: ["close", "open-login"],
  props: {
    showModalRegister: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      dialog: this.showModalRegister,
      visible: false,
      nomeTitular: "",
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
      senha: "",
      senhaNovamente: "",
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
      relationship: [
        "Pai/Mãe",
        "Filho(a)",
        "Neto(a)",
        "Irmã(o)",
        "Cônjuge",
        "Outros",
      ],
      rules: {
        required: (value) => !!value || "Este campo é obrigatório.",
        nome: (value) =>
          (/^[A-Za-zÀ-ÿ]{2,}( [A-Za-zÀ-ÿ]{2,})+$/.test(value) &&
            value.length <= 120) ||
          "Digite pelo menos um nome e um sobrenome.",
        nomeSocial: (value) =>
          value.length <= 120 || "Nome social muito longo.",
        cpf: (value) => this.validateCPF(value) || "CPF inválido.",
        cpfTitularDiferenteDependente: (value) => {
          const cpfTitular = this.cpf;
          const cpfDependentes = this.dependentes.map(
            (dependente) => dependente.cpf
          );

          if (cpfDependentes.includes(cpfTitular)) {
            return "CPF do(a) dependente não pode ser igual ao CPF do(a) titular.";
          }
          return true;
        },
        cpfUnicoDependentes: (value) => {
          const cpfDependentes = this.dependentes.map(
            (dependente) => dependente.cpf
          );

          // Verifica se há CPF duplicado
          const setCpfs = new Set(cpfDependentes);
          if (setCpfs.size !== cpfDependentes.length) {
            return "Dependentes não podem conter o mesmo CPF.";
          }
          return true;
        },
        email: (value) =>
          (/.+@.+\..+/.test(value) && value.length <= 80) || "E-mail inválido.",
        emailTitularDiferenteDependente: (value) => {
          const emailTitular = this.email;
          const emailsDependentes = this.dependentes.map(
            (dependente) => dependente.email
          );

          if (emailsDependentes.includes(emailTitular)) {
            return "E-mail do(a) dependente não pode ser igual ao e-mail do(a) titular.";
          }
          return true;
        },
        emailUnicoDependentes: (value) => {
          const emailsDependentes = this.dependentes.map(
            (dependente) => dependente.email
          );

          // Verifica se há e-mail duplicado
          const setEmails = new Set(emailsDependentes);
          if (setEmails.size !== emailsDependentes.length) {
            return "Dependentes não podem conter o mesmo e-mail.";
          }
          return true;
        },

        senhaIgual: (value) =>
          value === this.senha || "As senhas não coincidem.",
        senhaTamanho: (value) =>
          (value.length >= 6 && value.length <= 32) ||
          "Sua senha deve ter pelo menos 6 caracteres e no máximo 32.",
        dataNascimento: (value) => this.validateDataNascimento(value),
        telefone: (value) => this.validateTelefone(value),
        cep: (value) => this.validateCEP(value),
        endereco: (value) =>
          (/^(?!.*\s{2,})[a-zA-ZáàâãéêíóôõúüäëïöçÁÀÂÃÉÊÍÓÔÕÚÜÄËÏÖÇ0-9\s.,\'\-\/]{2,60}$/.test(
            value
          ) &&
            value.length <= 60) ||
          "Endereço inválido.",
        numeroContrato: (value) =>
          !value ||
          (/^\d{12}$/.test(value) && value.length === 12) ||
          "Número de contrato inválido.",
        numero: (value) =>
          (/^[0-9A-Za-zÀ-ÖØ-öø-ÿ\/-]+( [0-9A-Za-zÀ-ÖØ-öø-ÿ\/-]+)*$/.test(
            value
          ) &&
            value.length <= 10) ||
          "Número inválido.",
        bairro: (value) =>
          (/^(?!.*\s{2,})[A-Za-zÀ-ÖØ-öø-ÿ0-9\-]+(?:\s[A-Za-zÀ-ÖØ-öø-ÿ0-9\-]+)*$/.test(
            value
          ) &&
            value.length <= 60) ||
          "Bairro inválido.",
        cidade: (value) =>
          (/^[A-Za-zÀ-ÖØ-öø-ÿ]{3,}(?:[ -][A-Za-zÀ-ÖØ-öø-ÿ]{2,})*$/.test(
            value
          ) &&
            value.length <= 60) ||
          "Cidade inválida.",
        estado: (value) =>
          (/^[A-Z]{2}$/.test(value) && value.length <= 2) || "Estado inválido.",
        parentesco: (value) =>
          value.length <= 30 || "Grau de parentesco inválido.",
        emoji: (value) =>
          !/(?:\p{Extended_Pictographic})/u.test(value) ||
          "Caractere inválido.",
      },
      snackbar: false,
      snackbarText: "",
      snackbarColor: "",
    };
  },
  watch: {
    showModalRegister(val) {
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
    openModalLogin() {
      this.$emit("open-login"); // Emitindo evento para abrir o modal de login
    },
    validateCEP(value) {
      if (!value) return true;

      // Verifica o formato do CEP
      const cepRegex = /^\d{5}-\d{3}$/;
      if (!cepRegex.test(value)) {
        return "CEP inválido.";
      }

      if (value.length > 9) {
        return "CEP inválido.";
      }

      return true;
    },
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
    validateDataNascimento(value) {
      if (!value) return true;

      // Verifica o formato da data
      const dateRegex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
      if (!dateRegex.test(value)) {
        return "Data inválida.";
      }

      const [day, month, year] = value.split("/").map(Number);
      const birthDate = new Date(year, month - 1, day);

      // Verifica se a data é válida
      if (
        birthDate.getDate() !== day ||
        birthDate.getMonth() !== month - 1 ||
        birthDate.getFullYear() !== year
      ) {
        return "Data inválida.";
      }

      const today = new Date();
      const minDate = new Date(
        today.getFullYear() - 200,
        today.getMonth(),
        today.getDate()
      );

      if (birthDate < minDate || birthDate > today) {
        return "Data inválida.";
      }

      if (value.length > 10) {
        return "Data inválida.";
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
      this.dialog = false;
      this.$emit("close");
    },
    addDependent() {
      this.dependentes.push({
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
        endereço: "",
        numero: "",
        complemento: "",
        bairro: "",
        cidade: "",
        estado: "",
      });
    },
    removeDependent(index) {
      this.dependentes.splice(index, 1);
    },
    resetFields() {
      this.nomeTitular = "";
      this.nomeSocial = "";
      this.cpf = "";
      this.dataNascimento = "";
      this.email = "";
      this.telefone1 = "";
      this.telefone2 = "";
      (this.numeroContrato = ""), (this.cep = "");
      this.endereco = "";
      this.numero = "";
      this.complemento = "";
      this.bairro = "";
      this.cidade = "";
      this.estado = "";
      this.senha = "";
      this.senhaNovamente = "";
      this.dependentes = [
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
          endereço: "",
          numero: "",
          complemento: "",
          bairro: "",
          cidade: "",
          estado: "",
        },
      ];
      this.visible = false;
    },
    fillAddress(cep) {
      if (cep.length === 9) {
        this.fetchAddress();
      } else {
        this.resetAddressFields();
      }
    },
    fillAddressDependente(cep, index) {
      if (cep.length === 9) {
        this.fetchDependentAddress(index);
      } else {
        this.resetDependentAddressFields(index);
      }
    },
    async fetchAddress() {
      try {
        const response = await axios.get(
          `https://viacep.com.br/ws/${this.cep.replace("-", "")}/json/`
        );
        const data = response.data;
        if (!data.erro) {
          this.endereco = data.logradouro;
          this.bairro = data.bairro;
          this.cidade = data.localidade;
          this.estado = data.uf;
        } else {
          this.resetAddressFields();
          alert("CEP não encontrado.");
        }
      } catch (error) {
        console.error("Erro ao buscar o endereço:", error);
        this.resetAddressFields();
      }
    },
    async fetchDependentAddress(index) {
      try {
        const response = await axios.get(
          `https://viacep.com.br/ws/${this.dependentes[index].cep.replace(
            "-",
            ""
          )}/json/`
        );
        const data = response.data;
        if (!data.erro) {
          this.dependentes[index].endereco = data.logradouro;
          this.dependentes[index].bairro = data.bairro;
          this.dependentes[index].cidade = data.localidade;
          this.dependentes[index].estado = data.uf;
        } else {
          this.resetDependentAddressFields(index);
          alert("CEP do dependente não encontrado.");
        }
      } catch (error) {
        console.error("Erro ao buscar o endereço do dependente:", error);
        this.resetDependentAddressFields(index);
      }
    },
    resetAddressFields() {
      this.endereco = "";
      this.bairro = "";
      this.cidade = "";
      this.estado = "";
      this.numero = "";
      this.complemento = "";
    },
    resetDependentAddressFields(index) {
      this.dependentes[index].endereco = "";
      this.dependentes[index].bairro = "";
      this.dependentes[index].cidade = "";
      this.dependentes[index].estado = "";
      this.dependentes[index].numero = "";
      this.dependentes[index].complemento = "";
    },
    async submitForm() {
      const isValid = await this.$refs.formCadastro.validate();

      if (!isValid.valid) {
        return;
      }

      const formData = {
        nomeTitular: this.nomeTitular,
        nomeSocial: this.nomeSocial,
        cpf: this.cpf,
        dataNascimento: this.dataNascimento,
        email: this.email,
        telefone1: this.telefone1,
        telefone2: this.telefone2,
        numeroContrato: this.numeroContrato,
        cep: this.cep,
        endereco: this.endereco,
        numero: this.numero,
        complemento: this.complemento,
        bairro: this.bairro,
        cidade: this.cidade,
        estado: this.estado,
        senha: this.senha,
        dependentes: this.dependentes,
      };

      console.log(formData);

      await axios
        .post("http://localhost:8181/cadastro.php", formData)
        .then((response) => {
          console.log("Resposta do servidor:", response.data);
          if (response.data.status == "error") {
            this.snackbarText = response.data.message;
            this.snackbarColor = "error";
            this.snackbarIcon = "mdi-alert-circle";
            this.snackbar = true;
          } else {
            this.closeDialog();
            this.snackbarText = "Cadastro criado com sucesso";
            this.snackbarColor = "success";
            this.snackbarIcon = "mdi-check-circle";
            this.snackbar = true;
          }
        })
        .catch((error) => {
          console.error("Erro ao enviar o formulário:", error);
          this.snackbarText = "Erro ao salvar cadastro";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
        });
    },
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
