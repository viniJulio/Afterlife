<template>
  <div>
    <div class="container" style="max-width: 1200px; margin: 0 auto">
      <v-card-text class="pb-3">
        <v-form ref="formBusiness">
          <h4 class="pb-2 font-weight-bold">RESPONSÁVEL</h4>
          <v-row>
            <v-col cols="12" sm="6" md="4" class="pb-0">
              <v-text-field
                label="Nome responsável"
                type="text"
                v-model="nomeResponsavel"
                autofocus
                required
                :rules="[rules.required, rules.nome, rules.emoji]"
                maxlength="120"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="4" class="pb-0">
              <v-text-field
                label="Telefone responsável"
                type="text"
                v-model="telefoneResponsavel"
                v-mask="'(##) #####-####'"
                required
                :rules="[rules.required, rules.telefone, rules.emoji]"
                maxlength="15"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols class="pb-0">
              <v-text-field
                label="E-mail responsável"
                type="email"
                v-model="emailResponsavel"
                required
                :rules="[rules.required, rules.email, rules.emoji]"
                maxlength="80"
                counter
              ></v-text-field>
            </v-col>
          </v-row>
          <h4 class="pb-2 pt-4 font-weight-bold">EMPRESA</h4>
          <div style="max-width: 1000px; margin: 0 auto">
            <v-row>
              <v-col cols="12" sm="6" md="6">
                <UploadLogo
                  ref="uploadLogo"
                  :logo="this.logo"
                  :button-text="'ADICIONAR LOGO'"
                />
              </v-col>
              <v-col cols="12" sm="6" md="6">
                <UploadLogo
                  ref="uploadLogoPequena"
                  :logo="this.logoPequena"
                  :button-text="'ADICIONAR FAVICON'"
                />
              </v-col>
            </v-row>
          </div>
          <div class="mt-5" style="height: 40px"></div>
          <v-row>
            <span style="margin: 0 auto">Tipo de unidade</span>
            <v-col cols="12" class="pb-0 pt-0 text-center">
              <v-radio-group
                v-model="tipoEmpresa"
                :rules="[rules.required]"
                required
                inline
                class="d-flex justify-center align-center"
              >
                <v-radio label="Matriz" value="matriz"></v-radio>
                <v-radio label="Filial" value="filial"></v-radio>
              </v-radio-group>
            </v-col>

            <v-col cols="12" sm="6" md="5" class="pb-0">
              <v-text-field
                label="Razão social"
                type="text"
                v-model="razaoSocial"
                required
                :rules="[rules.required, rules.razaoSocial, rules.emoji]"
                maxlength="120"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="4" class="pb-0">
              <v-text-field
                label="Nome Fantasia"
                type="text"
                v-model="nomeFantasia"
                :rules="[rules.nomeFantasia, rules.emoji]"
                maxlength="120"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="3" class="pb-0">
              <v-text-field
                label="CNPJ"
                type="text"
                v-model="cnpj"
                v-mask="'##.###.###/####-##'"
                required
                :rules="[rules.required, rules.cnpj, rules.emoji]"
                maxlength="18"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="3" class="pb-0">
              <v-text-field
                label="Inscrição estadual"
                type="text"
                v-model="inscEstadual"
                v-mask="'###############'"
                :rules="[rules.emoji, rules.inscricao]"
                maxlength="15"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="3" class="pb-0">
              <v-text-field
                label="Inscrição municipal"
                type="text"
                v-model="inscMunicipal"
                v-mask="'###############'"
                :rules="[rules.emoji, rules.inscricao]"
                maxlength="15"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="6" class="pb-0">
              <v-text-field
                label="E-mail"
                type="email"
                v-model="email"
                required
                :rules="[rules.required, rules.email, rules.emoji]"
                maxlength="80"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="6" sm="4" md="3" class="pb-0">
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
            <v-col cols="6" sm="4" md="3" class="pb-0">
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
            <v-col cols="12" sm="4" md="2" class="pb-0">
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
            <v-col cols="12" sm="8" md="4" class="pb-0">
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
            <v-col cols="12" sm="4" md="3" class="pb-0">
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
            <v-col cols="12" sm="6" md="4" class="pb-0">
              <v-text-field
                label="Complemento"
                type="text"
                v-model="complemento"
                :rules="[rules.emoji]"
                maxlength="80"
                counter
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="5" class="pb-0">
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
            <v-col cols="12" sm="6" md="6" class="pb-0">
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
            <v-col cols="12" sm="6" md="6" class="pb-0">
              <v-text-field
                v-model="estado"
                type="text"
                label="Estado"
                required
                :rules="[rules.required, rules.estado, rules.emoji]"
                maxlength="2"
                counter
                disabled
              ></v-text-field>
            </v-col>
            <v-col cols="6" sm="6" md="3" class="pb-0">
              <v-text-field
                v-model="instagram"
                type="text"
                label="Instagram"
                :rules="[rules.redeSocial, rules.emoji]"
                maxlength="30"
                counter
              >
                <template v-slot:prepend>
                  <font-awesome-icon :icon="['fab', 'instagram']" />
                </template>
              </v-text-field>
            </v-col>
            <v-col cols="6" sm="6" md="3" class="pb-0">
              <v-text-field
                v-model="facebook"
                type="text"
                label="Facebook"
                :rules="[rules.redeSocial, rules.emoji]"
                maxlength="50"
                counter
              >
                <template v-slot:prepend>
                  <font-awesome-icon :icon="['fab', 'facebook']" />
                </template>
              </v-text-field>
            </v-col>
            <v-col cols="6" sm="6" md="3" class="pb-0">
              <v-text-field
                v-model="twitter"
                type="text"
                label="X/Twitter"
                :rules="[rules.redeSocial, rules.emoji]"
                maxlength="15"
                counter
              >
                <template v-slot:prepend>
                  <font-awesome-icon :icon="['fab', 'x-twitter']" />
                </template>
              </v-text-field>
            </v-col>
            <v-col cols="6" sm="6" md="3" class="pb-0">
              <v-text-field
                v-model="bluesky"
                type="text"
                label="Bluesky"
                :rules="[rules.redeSocial, rules.emoji]"
                maxlength="55"
                counter
              >
                <template v-slot:prepend>
                  <font-awesome-icon :icon="['fab', 'bluesky']" />
                </template>
              </v-text-field>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
      <v-card-actions class="d-flex justify-center">
        <v-btn class="btnGreen" rounded="0" text @click="saveItem"
          >Salvar</v-btn
        >
      </v-card-actions>
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
  </div>
</template>

<script>
import axios from "axios";
import UploadLogo from "../admin/UploadLogo.vue";

export default {
  data() {
    return {
      search: "",
      idEmpresa: "",
      nomeResponsavel: "",
      emailResponsavel: "",
      telefoneResponsavel: "",
      razaoSocial: "",
      nomeFantasia: "",
      cnpj: "",
      inscEstadual: "",
      inscMunicipal: "",
      cep: "",
      endereco: "",
      numero: "",
      complemento: "",
      bairro: "",
      cidade: "",
      estado: "",
      telefone1: "",
      telefone2: "",
      email: "",
      logo: "",
      logoPequena: "",
      tipoEmpresa: "",
      redesSociais: [],
      twitter: "",
      instagram: "",
      facebook: "",
      bluesky: "",
      snackbar: false,
      snackbarText: "",
      snackbarColor: "",
      snackbarIcon: "",
      rules: {
        required: (value) => !!value || "Este campo é obrigatório.",
        email: (value) =>
          (/.+@.+\..+/.test(value) && value.length <= 80) || "E-mail inválido.",
        nome: (value) =>
          (/^[A-Za-zÀ-ÿ]{2,}( [A-Za-zÀ-ÿ]{2,})+$/.test(value) &&
            value.length <= 120) ||
          "Digite pelo menos um nome e um sobrenome.",
        razaoSocial: (value) =>
          this.validateRazaoSocial(value) || "Razão Social inválida.",
        nomeFantasia: (value) =>
          this.validateNomeFantasia(value) || "Nome Fantasia inválido.",
        cnpj: (value) => this.validateCNPJ(value) || "CNPJ inválido.",
        inscricao: (value) =>
          this.validateInscricao(value) || "Inscrição inválida.",
        telefone: (value) => this.validateTelefone(value),
        cep: (value) => this.validateCEP(value),
        endereco: (value) =>
          (/^(?!.*\s{2,})[a-zA-ZáàâãéêíóôõúüäëïöçÁÀÂÃÉÊÍÓÔÕÚÜÄËÏÖÇ0-9\s.,\'\-\/]{2,60}$/.test(
            value
          ) &&
            value.length <= 60) ||
          "Endereço inválido.",
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
        emoji: (value) =>
          !/(?:\p{Extended_Pictographic})/u.test(value) ||
          "Caractere inválido.",
        redeSocial: (value) => {
          // Se o valor estiver vazio, retorna true (não há erro)
          if (!value) return true;

          const redeSocialRegex = /^[a-zA-Z0-9][a-zA-Z0-9._-]{0,54}$/;
          return redeSocialRegex.test(value) || "Nome de usuário inválido";
        },
      },
    };
  },
  components: { UploadLogo },
  methods: {
    formatCnpj(cnpj) {
      // Remove qualquer caractere não numérico
      cnpj = cnpj.replace(/\D/g, "");

      // Aplica a máscara no CNPJ (##.###.###/####-##)
      if (cnpj.length === 14) {
        return cnpj.replace(
          /(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/,
          "$1.$2.$3/$4-$5"
        );
      }

      return cnpj; // Retorna o CNPJ sem formatação se o tamanho for inválido
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
    validateInscricao(value) {
      // Se o campo está vazio, não realizar validação
      if (!value) return true;

      // Verifica se não há letras e se o comprimento está entre 8 e 15
      const noLetters = /^[^a-zA-Z]*$/.test(value);
      const validLength = value.length >= 8 && value.length <= 15;

      return noLetters && validLength;
    },
    validateRazaoSocial(value) {
      if (!value) return false;

      // Verifica se o valor tem pelo menos 3 caracteres
      if (value.length < 3) return false;

      // Verifica se contém apenas caracteres permitidos
      const regex = /^(?!.*\s{2,})[A-Za-z0-9\s.,-/&'À-ÿ]*$/;
      return regex.test(value);
    },
    validateNomeFantasia(value) {
      // Verifica se contém apenas caracteres permitidos
      const regex = /^(?!.*\s{2,})[A-Za-z0-9\s.,-/&'À-ÿ]*$/;
      return regex.test(value);
    },
    validateCNPJ(value) {
      if (!value) return false;

      // Remove caracteres não numéricos
      const cnpj = value.replace(/[^\d]+/g, "");

      if (cnpj.length !== 14) return false;

      // Verifica se todos os dígitos são iguais
      if (/^(\d)\1+$/.test(cnpj)) return false;

      // Validação dos dígitos verificadores
      let tamanho = cnpj.length - 2;
      let numeros = cnpj.substring(0, tamanho);
      const digitos = cnpj.substring(tamanho);
      let soma = 0;
      let pos = tamanho - 7;

      for (let i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2) pos = 9;
      }

      let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
      if (resultado !== parseInt(digitos.charAt(0))) return false;

      tamanho++;
      numeros = cnpj.substring(0, tamanho);
      soma = 0;
      pos = tamanho - 7;

      for (let i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2) pos = 9;
      }

      resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
      return resultado === parseInt(digitos.charAt(1));
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
    updateRedesSociais(socialMedia, value) {
      // Remove a rede social do array, caso o valor seja vazio
      if (!value) {
        const index = this.redesSociais.findIndex((item) =>
          item.hasOwnProperty(socialMedia)
        );
        if (index !== -1) {
          this.redesSociais.splice(index, 1);
        }
      } else {
        // Adiciona ou atualiza a rede social no array
        const index = this.redesSociais.findIndex((item) =>
          item.hasOwnProperty(socialMedia)
        );
        if (index === -1) {
          this.redesSociais.push({ [socialMedia]: value });
        } else {
          this.redesSociais[index][socialMedia] = value;
        }
      }
    },
    async saveItem() {
      const logo = this.$refs.uploadLogo.getImageFiles();
      const logoPequena = this.$refs.uploadLogoPequena.getImageFiles();
      if (!Array.isArray(logo) || !Array.isArray(logoPequena)) {
        console.error("getImageFiles não retornou um array.");
        return;
      }

      const isValid = await this.$refs.formBusiness.validate();

      if (!isValid.valid) {
        return;
      }

      this.redesSociais = [];

      // Adicionar redes sociais ao array
      this.updateRedesSociais("Twitter", this.twitter);
      this.updateRedesSociais("Instagram", this.instagram);
      this.updateRedesSociais("Facebook", this.facebook);
      this.updateRedesSociais("Bluesky", this.bluesky);

      const formData = new FormData();

      // Adicione os dados da empresa ao formData
      formData.append("idEmpresa", this.idEmpresa ?? "");
      formData.append("nomeResponsavel", this.nomeResponsavel.trim());
      formData.append("nomeFantasia", this.nomeFantasia.trim());
      formData.append("emailResponsavel", this.emailResponsavel.trim());
      formData.append("telefoneResponsavel", this.telefoneResponsavel);
      formData.append("razaoSocial", this.razaoSocial.trim());
      formData.append("cnpj", this.cnpj);
      formData.append("inscEstadual", this.inscEstadual);
      formData.append("inscMunicipal", this.inscMunicipal);
      formData.append("cep", this.cep);
      formData.append("endereco", this.endereco.trim());
      formData.append("numero", this.numero.trim());
      formData.append("complemento", this.complemento.trim());
      formData.append("bairro", this.bairro.trim());
      formData.append("cidade", this.cidade.trim());
      formData.append("estado", this.estado.trim());
      formData.append("telefone1", this.telefone1);
      formData.append("telefone2", this.telefone2);
      formData.append("email", this.email.trim());
      formData.append("tipoEmpresa", this.tipoEmpresa);
      formData.append("redesSociais", JSON.stringify(this.redesSociais));

      // Se houver logo, adicioná-la ao formData
      if (logo.length > 0 && logoPequena.length > 0) {
        formData.append("logo", logo[0]);
        formData.append("logoPequena", logoPequena[0]);
      } else {
        this.snackbarText = "Os dois tipos de logo são obrigatórios.";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
        this.disabledButton = false;
        return;
      }

      try {
        const response = await axios.post(
          "http://localhost:8181/empresas.php",
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          }
        );

        if (response.status !== 200) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }

        localStorage.setItem(
          "snackbarMessage",
          JSON.stringify({
            text: this.idEmpresa
              ? "Empresa alterada com sucesso"
              : "Empresa criada com sucesso",
            color: "success",
            icon: "mdi-check-circle",
          })
        );

        if (response.data.status == "error") {
          throw new Error(response.data);
        } else {
          window.location.reload(true);
        }
      } catch (error) {
        this.snackbarText = "Erro ao salvar dados da empresa";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
      }
    },
    async fetchBusiness() {
      try {
        const response = await axios.get("http://localhost:8181/empresas.php");

        if (!response.data[0]) {
          return;
        }

        const dados = response.data[0];

        console.log(dados.caminhoLogo, dados.caminhoLogoPequena);

        this.idEmpresa = dados.idEmpresa;
        this.nomeResponsavel = dados.nomeResponsavel;
        this.nomeFantasia = dados.nomeFantasia ?? "";
        this.telefoneResponsavel = dados.telefoneResponsavel;
        this.emailResponsavel = dados.emailResponsavel;
        this.logo = dados.logo;
        this.tipoEmpresa = dados.tipoEmpresa;
        this.razaoSocial = dados.razaoSocial;
        this.cnpj = dados.cnpj;
        this.inscEstadual = dados.inscEstadual;
        this.inscMunicipal = dados.inscMunicipal;
        this.cep = dados.cep;
        this.endereco = dados.endereco;
        this.numero = dados.numero;
        this.complemento = dados.complemento;
        this.bairro = dados.bairro;
        this.cidade = dados.cidade;
        this.estado = dados.estado;
        this.telefone1 = dados.telefone1;
        this.telefone2 = dados.telefone2;
        this.email = dados.email;
        this.logo = dados.caminhoLogo;
        this.logoPequena = dados.caminhoLogoPequena;
        this.redesSociais = dados.redesSociais;

        if (typeof dados.redesSociais === "string") {
          const parsedData = JSON.parse(dados.redesSociais);

          // Agora você pode acessar os dados de cada plataforma
          parsedData.forEach((item) => {
            const platform = Object.keys(item)[0]; // nome da plataforma (ex: Twitter, Instagram, etc.)
            const value = item[platform]; // o valor associado à plataforma

            // Atribuindo os valores para as variáveis específicas
            if (platform === "Twitter") this.twitter = value;
            if (platform === "Instagram") this.instagram = value;
            if (platform === "Facebook") this.facebook = value;
            if (platform === "Bluesky") this.bluesky = value;
          });
        }
      } catch (error) {
        console.error("Error:", error);
        this.snackbarText = "Erro ao carregar informações da empresa";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
      }
    },
    fillAddress(cep) {
      if (cep.length === 9) {
        this.fetchAddress();
      } else {
        this.resetAddressFields();
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
          this.snackbarText = "CEP não encontrado.";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
        }
      } catch (error) {
        console.error("Erro ao buscar o endereço:", error);
        this.resetAddressFields();
        this.snackbarText = "Erro ao buscar o CEP. Verifique sua conexão.";
        this.snackbarColor = "error";
        this.snackbarIcon = "mdi-alert-circle";
        this.snackbar = true;
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
  },
  async mounted() {
    await this.fetchBusiness();
    const snackbarMessage = localStorage.getItem("snackbarMessage");

    if (snackbarMessage) {
      const { text, color, icon } = JSON.parse(snackbarMessage);
      this.snackbarText = text;
      this.snackbarColor = color;
      this.snackbarIcon = icon;
      this.snackbar = true;

      // Limpar a mensagem do snackbar após exibição
      localStorage.removeItem("snackbarMessage");
    }
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
</style>
