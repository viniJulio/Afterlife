<template>
  <v-dialog v-model="dialog" max-width="1000px">
    <v-card class="pa-2">
      <v-card-title>Editar Dados</v-card-title>
      <v-form @submit.prevent="submitForm()" ref="formConfiguracao">
        <v-card-text class="pb-0">
          <h4 class="pb-2 font-weight-bold">TITULAR</h4>
          <v-row>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Nome completo"
                type="text"
                v-model="titular.nomeTitular"
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
                v-model="titular.nomeSocial"
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
                v-model="titular.cpf"
                v-mask="'###.###.###-##'"
                :rules="[
                  rules.required,
                  rules.cpf,
                  rules.emoji,
                  rules.cpfTitularDiferenteDependente,
                ]"
                maxlength="14"
                counter
                disabled
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Data de nascimento"
                type="text"
                required
                v-model="titular.dataNascimento"
                v-mask="'##/##/####'"
                :rules="[rules.required, rules.dataNascimento, rules.emoji]"
                maxlength="10"
                counter
                disabled
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="E-mail"
                type="email"
                required
                v-model="titular.email"
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
                v-model="titular.telefone1"
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
                v-model="titular.telefone2"
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
                v-model="titular.numeroContrato"
                :rules="[rules.numeroContrato, rules.required, rules.emoji]"
                maxlength="12"
                counter
                disabled
                readonly
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="CEP"
                type="text"
                required
                v-model="titular.cep"
                v-mask="'#####-###'"
                :rules="[rules.required, rules.cep, rules.emoji]"
                maxlength="9"
                counter
                @input="fillAddress(titular.cep)"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Endereço"
                type="text"
                required
                v-model="titular.endereco"
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
                v-model="titular.numero"
                :rules="[rules.required, rules.numero, rules.emoji]"
                maxlength="10"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Complemento"
                type="text"
                v-model="titular.complemento"
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
                v-model="titular.bairro"
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
                v-model="titular.cidade"
                :rules="[rules.required, rules.cidade, rules.emoji]"
                maxlength="60"
                counter
                disabled
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6" class="pb-0">
              <v-text-field
                label="Estado"
                v-model="titular.estado"
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
                v-model="titular.senha"
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
                v-model="titular.senhaNovamente"
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
          <div v-for="(dependente, index) in titular.dependentes" :key="index">
            <div class="d-flex justify-space-between pt-1">
              <h4 class="pt-4 pb-2">DEPENDENTE {{ index + 1 }}</h4>
              <v-tooltip
                text="Excluir dependente"
                location="bottom"
                v-if="titular.dependentes.length > 1"
              >
                <template v-slot:activator="{ props }">
                  <v-btn
                    style="color: #ef5350"
                    v-bind="props"
                    icon="mdi-delete"
                    elevation="0"
                    @click="removeDependent(index)"
                  ></v-btn>
                </template>
              </v-tooltip>
            </div>
            <v-row>
              <v-col cols="12" md="4" class="pb-0">
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
              <v-col cols="12" md="4" class="pb-0">
                <v-text-field
                  label="Nome social"
                  type="text"
                  v-model="dependente.nomeSocial"
                  :rules="[rules.nomeSocial, rules.emoji]"
                  maxlength="120"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="4" class="pb-0">
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
              <v-col cols="12" md="4" class="pb-0">
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
                md="4"
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
              <v-col cols="12" md="4" class="pb-0">
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
              <v-col cols="12" md="4" class="pb-0">
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
              <v-col cols="12" md="4" class="pb-0">
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
              <v-col cols="12" md="4" class="pb-0">
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
              <v-col cols="12" md="4" class="pb-0">
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
              <v-col cols="12" md="4" class="pb-0">
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
              <v-col cols="12" md="4" class="pb-0">
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
              <v-col cols="12" md="4" class="pb-0">
                <v-text-field
                  label="Complemento"
                  type="text"
                  v-model="dependente.complemento"
                  :rules="[rules.emoji]"
                  maxlength="80"
                  counter
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="4" class="pb-0">
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
              <v-col cols="12" md="4" class="pb-0">
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
  name: "ModalSetting",
  emits: ["close"],
  props: {
    showModalSetting: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      dialog: this.showModalSetting,
      visible: false,
      titular: {
        id: "",
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
        dependentes: [
          {
            id: "",
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
      baseUrl: "http://localhost:8181/",
      senhaNovamente: "",
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
          const cpfTitular = this.titular.cpf;
          const cpfDependentes = this.titular.dependentes.map(
            (dependente) => dependente.cpf
          );

          if (cpfDependentes.includes(cpfTitular)) {
            return "CPF do(a) dependente não pode ser igual ao CPF do(a) titular.";
          }
          return true;
        },
        cpfUnicoDependentes: (value) => {
          const cpfDependentes = this.titular.dependentes.map(
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
          const emailTitular = this.titular.email;
          const emailsDependentes = this.titular.dependentes.map(
            (dependente) => dependente.email
          );

          if (emailsDependentes.includes(emailTitular)) {
            return "E-mail do(a) dependente não pode ser igual ao e-mail do(a) titular.";
          }
          return true;
        },
        emailUnicoDependentes: (value) => {
          const emailsDependentes = this.titular.dependentes.map(
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
          value === this.titular.senha || "As senhas não coincidem.",
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
      disabledButton: false,
    };
  },
  watch: {
    showModalSetting(val) {
      this.dialog = val;
    },
    dialog(val) {
      if (!val) {
        this.$emit("close");
      } else {
        this.fetchDados();
      }
    },
  },
  methods: {
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
      this.titular.dependentes.push({
        id: "",
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
      });
    },
    removeDependent(index) {
      this.titular.dependentes.splice(index, 1);
    },
    resetFields() {
      this.titular.nomeTitular = "";
      this.titular.nomeSocial = "";
      this.titular.cpf = "";
      this.titular.dataNascimento = "";
      this.titular.email = "";
      this.titular.telefone1 = "";
      this.titular.telefone2 = "";
      this.titular.numeroContrato = "";
      this.titular.cep = "";
      this.titular.endereco = "";
      this.titular.numero = "";
      this.titular.complemento = "";
      this.titular.bairro = "";
      this.titular.cidade = "";
      this.titular.estado = "";
      this.titular.senha = "";
      this.titular.senhaNovamente = "";
      this.titular.dependentes = [
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
          `https://viacep.com.br/ws/${this.titular.cep.replace("-", "")}/json/`
        );
        const data = response.data;
        if (!data.erro) {
          this.titular.endereco = data.logradouro;
          this.titular.bairro = data.bairro;
          this.titular.cidade = data.localidade;
          this.titular.estado = data.uf;
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
      if (this.titular.dependentes[index].cep.length === 9) {
        try {
          const response = await axios.get(
            `https://viacep.com.br/ws/${this.titular.dependentes[
              index
            ].cep.replace("-", "")}/json/`
          );
          const data = response.data;
          if (!data.erro) {
            this.titular.dependentes[index].endereco = data.logradouro;
            this.titular.dependentes[index].bairro = data.bairro;
            this.titular.dependentes[index].cidade = data.localidade;
            this.titular.dependentes[index].estado = data.uf;
          } else {
            this.resetDependentAddressFields(index);
            alert("CEP do dependente não encontrado.");
          }
        } catch (error) {
          console.error("Erro ao buscar o endereço do dependente:", error);
          this.resetDependentAddressFields(index);
        }
      }
    },
    resetAddressFields() {
      this.titular.endereco = "";
      this.titular.bairro = "";
      this.titular.cidade = "";
      this.titular.estado = "";
      this.titular.numero = "";
      this.titular.complemento = "";
    },
    resetDependentAddressFields(index) {
      this.titular.dependentes[index].endereco = "";
      this.titular.dependentes[index].bairro = "";
      this.titular.dependentes[index].cidade = "";
      this.titular.dependentes[index].estado = "";
      this.titular.dependentes[index].numero = "";
      this.titular.dependentes[index].complemento = "";
    },
    async submitForm() {
      const isValid = await this.$refs.formConfiguracao.validate();

      if (!isValid.valid) {
        return;
      }

      this.disabledButton = true;

      const formData = {
        idPessoa: this.titular.id,
        nomeTitular: this.titular.nomeTitular,
        nomeSocial: this.titular.nomeSocial,
        cpf: this.titular.cpf,
        dataNascimento: this.titular.dataNascimento,
        email: this.titular.email,
        telefone1: this.titular.telefone1,
        telefone2: this.titular.telefone2,
        numeroContrato: this.titular.numeroContrato,
        cep: this.titular.cep,
        endereco: this.titular.endereco,
        numero: this.titular.numero,
        complemento: this.titular.complemento,
        bairro: this.titular.bairro,
        cidade: this.titular.cidade,
        estado: this.titular.estado,
        senha: this.titular.senha,
        dependentes: this.titular.dependentes,
      };

      console.log(formData);

      axios
        .post("http://localhost:8181/cadastro.php", formData)
        .then((response) => {
          console.log("Resposta do servidor:", response.data);
          this.closeDialog();
          this.disabledButton = false;
          this.snackbarText = "Cadastro alterado com sucesso";
          this.snackbarColor = "success";
          this.snackbarIcon = "mdi-check-circle";
          this.snackbar = true;
        })
        .catch((error) => {
          console.error("Erro ao enviar o formulário:", error);
          this.disabledButton = false;
          this.snackbarText = "Erro ao salvar cadastro";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
        });
    },
    async fetchDados() {
      try {
        const response = await axios.get(
          this.baseUrl + "pessoas.php?acao=alteracao",
          {
            withCredentials: true,
          }
        );

        // Remove o texto inicial e mantém apenas o JSON
        const jsonString = response.data.replace(/^.*?{/, "{");
        const dados = JSON.parse(jsonString);
        console.log(dados);
        // Define os dados no objeto `titular`
        this.titular = {
          id: dados.idPessoa,
          nomeTitular: dados.nomeTitular,
          nomeSocial: dados.nomeSocial || "",
          cpf: dados.cpf,
          dataNascimento: dados.dataNascimento,
          email: dados.email,
          telefone1: dados.telefone1,
          telefone2: dados.telefone2 || "",
          numeroContrato: dados.numeroContrato || "",
          cep: dados.cep,
          endereco: dados.endereco,
          numero: dados.numero,
          complemento: dados.complemento || "",
          bairro: dados.bairro,
          cidade: dados.cidade,
          estado: dados.estado,
          dependentes: dados.dependentes.map((dado) => ({
            idPessoa: dado.idDependente,
            nome: dado.nome,
            nomeSocial: dado.nomeSocial || "",
            email: dado.email,
            relacionamento: this.relationship.includes(dado.relacionamento)
              ? dado.relacionamento
              : "Outros",
            outroRelacionamento: this.relationship.includes(dado.relacionamento)
              ? null
              : dado.relacionamento,
            cpf: dado.cpf,
            dataNascimento: dado.dataNascimento,
            telefone1: dado.telefone1,
            telefone2: dado.telefone2,
            cep: dado.cep,
            endereco: dado.endereco,
            numero: dado.numero,
            complemento: dado.complemento,
            bairro: dado.bairro,
            cidade: dado.cidade,
            estado: dado.estado,
          })),
        };
      } catch (error) {
        console.error("Erro ao carregar:", error);
        this.snackbarText = "Erro ao carregar";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
      }
    },
  },
  mounted() {
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
