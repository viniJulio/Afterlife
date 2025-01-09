<template>
  <v-container>
    <div class="container pb-0">
      <v-card>
        <v-card-title class="header">
          <div class="title">
            <v-tooltip text="Voltar" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn
                  v-bind="props"
                  icon="mdi-chevron-left"
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
            @input="searchAudios"
          ></v-text-field>
        </v-card-title>
        <v-data-table
          :headers="headers"
          :items="items"
          :search="search"
          class="elevation-1"
          items-per-page-text="itens por página"
          pageText="{0}-{1} de {2}"
          no-data-text="Sem áudios salvos"
        >
          <!-- Slot onde será renderizado o botão de play -->
          <template v-slot:[`item.play`]="{ item }">
            <div class="icons">
              <v-tooltip text="Escutar" location="bottom">
                <template #activator="{ props }">
                  <v-btn
                    v-bind="props"
                    elevation="0"
                    size="small"
                    @click="playAudioTable(item)"
                  >
                    <v-icon
                      :icon="
                        isPlayingOnDatatable && this.playItem == item.idArquivos
                          ? 'mdi-pause'
                          : 'mdi-play'
                      "
                    />
                  </v-btn>
                </template>
              </v-tooltip>
            </div>
            <audio
              ref="audioElementDatatable"
              @ended="onAudioEndedTable"
            ></audio>
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
                    v-bind="props"
                    icon="mdi-delete"
                    elevation="0"
                    size="small"
                    style="color: #ef5350"
                    @click="openDeleteModal(item)"
                  ></v-btn>
                </template>
              </v-tooltip>
            </div>
          </template>
        </v-data-table>
      </v-card>
      <div v-if="isTitular" class="bottons">
        <v-btn
          rounded="0"
          class="btn-gravar-audio me-4"
          @click="openRecordAudioDialog"
          ><span class="mdi mdi-microphone-plus"></span>Gravar áudio</v-btn
        >
        <v-btn rounded="0" class="btn-novo-audio" @click="openNewAudioDialog"
          ><span class="mdi mdi-plus"></span>Adicionar áudio</v-btn
        >
      </div>
    </div>

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

    <!-- Modal for Delete Confirmation -->
    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card class="pa-1">
        <v-card-title class="headline">Excluir</v-card-title>
        <v-card-text>
          Você realmente deseja excluir o áudio
          <strong>{{ deletedItem.titulo }}</strong
          >?
        </v-card-text>
        <v-card-actions class="action">
          <v-btn class="btnCancel" rounded="0" text @click="closeDeleteDialog"
            >Cancelar</v-btn
          >
          <v-btn
            class="btn-salvar"
            rounded="0"
            text
            @click="deleteItemConfirmed(deletedItem.idArquivos)"
            >Excluir</v-btn
          >
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal for Add/Edit Record Audio -->
    <v-dialog v-model="recordDialog" max-width="1000px">
      <v-card class="pa-2">
        <v-card-title>
          <span class="headline">{{ dialogTitle }}</span>
        </v-card-title>
        <v-form @submit.prevent="submitRecordForm()" ref="formRecord">
          <v-card-text>
            <v-row>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  class="pb-0"
                  :readonly="!isTitular"
                  label="Título"
                  type="text"
                  v-model="editedItem.titulo"
                  :rules="[rules.required, rules.titulo, rules.emoji]"
                  counter
                  maxlength="80"
                  required
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  label="Descrição"
                  class="pb-0"
                  :readonly="!isTitular"
                  type="text"
                  v-model="editedItem.descricao"
                  :rules="[rules.required, rules.emoji]"
                  counter
                  required
                  no-resize
                ></v-textarea>
              </v-col>
              <v-col cols="12" class="pt-0">
                <div v-if="!editedItem.caminho">
                  <div v-if="audioBlob" class="audio-player mb-3">
                    <audio
                      ref="audioElement"
                      class="hidden"
                      :src="audioSrc"
                      @ended="onAudioEnded"
                    ></audio>

                    <v-btn
                      @click="togglePlayPause"
                      icon
                      size="x-small"
                      elevation="0"
                    >
                      <v-icon class="iconPlayer">
                        {{ isPlaying ? "mdi-pause" : "mdi-play" }}
                      </v-icon>
                    </v-btn>

                    <input
                      type="range"
                      min="0"
                      max="100"
                      step="1"
                      v-model="currentTimePercentage"
                      @input="seekAudio"
                      class="barPlayer"
                    />

                    <span class="timePlayer">
                      {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
                    </span>

                    <v-btn
                      @click="toggleMute"
                      icon
                      elevation="0"
                      size="x-small"
                    >
                      <v-icon class="iconVolume">
                        {{ isMuted ? "mdi-volume-off" : "mdi-volume-high" }}
                      </v-icon>
                    </v-btn>

                    <input
                      type="range"
                      min="0"
                      max="100"
                      step="1"
                      v-model="volumePercentage"
                      @input="adjustVolume"
                      class="volumePlayer"
                    />
                  </div>

                  <div class="w-100 pb-3">
                    <span v-if="isRecording" class="cronometro">{{
                      formattedTime
                    }}</span>
                    <v-tooltip
                      :text="
                        isRecording ? 'Parar Gravação' : 'Iniciar Gravação'
                      "
                      location="bottom"
                    >
                      <template v-slot:activator="{ props }">
                        <v-btn
                          v-bind="props"
                          icon="mdi-microphone"
                          id="recordAudio"
                          class="mic-off mx-auto d-flex"
                          @click="toggleRecord"
                        ></v-btn>
                      </template>
                    </v-tooltip>
                  </div>
                </div>

                <div v-else class="d-flex">
                  <v-tooltip text="Baixar áudio" location="bottom">
                    <template v-slot:activator="{ props }">
                      <v-btn
                        v-bind="props"
                        icon="mdi-tray-arrow-down"
                        id="recordAudio"
                        class="download mx-auto"
                        @click="downloadAudio"
                      ></v-btn>
                    </template>
                  </v-tooltip>
                </div>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="d-flex justify-center">
            <v-btn text rounded="0" @click="closeRecordDialog">{{
              isTitular ? "Cancelar" : "Fechar"
            }}</v-btn>
            <v-btn
              v-if="isTitular"
              class="btn-salvar"
              rounded="0"
              text
              type="submit"
              :disabled="disabledButton"
            >
              Salvar
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>

    <!-- Modal for Add/Edit Upload Audio -->
    <v-dialog v-model="uploadDialog" max-width="1000px">
      <v-card class="pa-2">
        <v-card-title>
          <span class="headline">{{ dialogTitle }}</span>
        </v-card-title>
        <v-form @submit.prevent="submitUploadForm" ref="formUpload">
          <v-card-text>
            <v-row>
              <v-col cols="12" class="pb-0">
                <v-text-field
                  class="pb-0"
                  :readonly="!isTitular"
                  label="Título"
                  type="text"
                  v-model="editedItem.titulo"
                  :rules="[rules.required, rules.titulo, rules.emoji]"
                  counter
                  maxlength="80"
                  required
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  label="Descrição"
                  class="pb-0"
                  :readonly="!isTitular"
                  type="text"
                  v-model="editedItem.descricao"
                  :rules="[rules.required, rules.emoji]"
                  counter
                  required
                  no-resize
                ></v-textarea>
              </v-col>

              <v-col cols="12" class="pt-0">
                <div v-if="!editedItem.idArquivos">
                  <div>
                    <div v-if="file" class="text-center">
                      <span>{{ file.name }}</span>
                    </div>
                    <div class="d-flex justify-space-between pt-1 pb-3">
                      <v-tooltip text="Escolher arquivo" location="bottom">
                        <template v-slot:activator="{ props }">
                          <v-file-input
                            class="d-none"
                            ref="fileInput"
                            v-model="file"
                            accept="audio/*"
                            label="Áudio"
                            hide-input
                            :rules="[rules.required]"
                          ></v-file-input>
                          <v-btn
                            v-bind="props"
                            class="mx-auto"
                            @click="triggerFileInput"
                            icon="mdi-paperclip"
                            color="#617A95"
                          ></v-btn>
                        </template>
                      </v-tooltip>
                    </div>
                  </div>
                </div>

                <div v-else class="d-flex">
                  <v-tooltip text="Baixar áudio" location="bottom">
                    <template v-slot:activator="{ props }">
                      <v-btn
                        v-bind="props"
                        icon="mdi-tray-arrow-down"
                        id="recordAudio"
                        color="#617A95"
                        class="mx-auto"
                        @click="downloadAudio"
                      ></v-btn>
                    </template>
                  </v-tooltip>
                </div>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="d-flex justify-center">
            <v-btn text rounded="0" @click="closeDialog">{{
              isTitular ? "Cancelar" : "Fechar"
            }}</v-btn>
            <v-btn
              v-if="isTitular"
              class="btn-salvar"
              rounded="0"
              text
              type="submit"
              :disabled="disabledButton"
            >
              Salvar
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import Swal from "sweetalert2";
import axios from "axios";
import { format, subHours } from "date-fns";

export default {
  data() {
    return {
      uploadDialog: false,
      recordDialog: false,
      dialogTitle: "",
      isMuted: false, // Adicione esta variável para controlar se o áudio está mudo
      previousVolume: 100,
      audioBlob: null,
      audioSrc: "",
      isPlaying: false,
      isPlayingOnDatatable: false,
      playItem: null,
      currentTime: 0,
      duration: 0,
      currentTimePercentage: 0,
      volumePercentage: 100,
      audioElement: null,
      audioElementDatatable: null,
      chunks: [],
      recorder: null,
      canRecord: true,
      errorMessage: null,
      file: null,
      newFile: null,
      search: "",
      deleteDialog: false,
      recordAudioDialog: false,
      baseUrl: "http://localhost:8181/",
      editedItem: {
        idArquivos: "",
        titulo: "",
        descricao: "",
        caminho: "",
      },
      deletedItem: null,
      headers: [
        { title: "", align: "center", value: "play", sortable: false },
        { title: "Título", align: "left", value: "titulo", sortable: true },
        {
          title: "Data de Criação",
          value: "dataCriacao",
          sortable: true,
        },
        {
          title: "Data de Atualização",
          value: "dataAtualizacao",
          sortable: true,
        },
        { title: "", value: "actions", align: "center", sortable: false },
      ],
      items: [],
      pasta: {
        id: this.$route.params.id,
        titulo: "",
      },
      editedIndex: -1,
      defaultItem: {
        idArquivos: "",
        titulo: "",
        descricao: "",
        caminho: "",
      },
      isRecording: false,
      secondsElapsed: 0,
      timer: null,
      chunks: [],
      recorder: null,
      canRecord: false,
      errorMessage: null,
      audioBlob: null,
      snackbar: false,
      snackbarText: "",
      snackbarColor: "",
      disabledButton: false,
      isTitular: null,
      rules: {
        required: (value) => !!value || "Este campo é obrigatório.",
        titulo: (value) =>
          value.length <= 80 || "Tamanho máximo do título: 80 caracteres.",
        emoji: (value) =>
          !/(?:\p{Extended_Pictographic})/u.test(value) ||
          "Caractere inválido.",
      },
    };
  },
  async created() {
    await this.folderExists();
    await this.checkTitular();
  },
  watch: {
    uploadDialog(val) {
      if (val) {
        this.setupAudio();
      }
    },
    audioBlob(newBlob) {
      if (newBlob) {
        this.audioSrc = URL.createObjectURL(newBlob);
        this.$nextTick(() => {
          this.audioElement = this.$refs.audioElement;
          this.audioElement.addEventListener("timeupdate", this.updateTime);
          this.audioElement.addEventListener("loadedmetadata", this.updateTime); // Atualiza a duração corretamente
        });
      }
    },
    search() {
      this.searchAudios();
    },
  },
  beforeDestroy() {
    if (this.audioElement) {
      this.audioElement.removeEventListener("timeupdate", this.updateTime);
    }
  },
  computed: {
    formattedTime() {
      const minutes = Math.floor(this.secondsElapsed / 60);
      const seconds = this.secondsElapsed % 60;
      return `${String(minutes).padStart(2, "0")}:${String(seconds).padStart(
        2,
        "0"
      )}`;
    },
  },
  methods: {
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
    formatDate(date) {
      // Subtrai 3 horas da data
      const adjustedDate = subHours(new Date(date), 3);
      // Formata a data no formato dd/mm/aaaa hh:mm
      return format(adjustedDate, "dd/MM/yyyy HH:mm");
    },
    navigateTo(route) {
      this.$router
        .push(route)
        .then(() => {
          console.log("Navegação bem-sucedida para:", route);
        })
        .catch((err) => {
          console.error("Erro ao navegar:", err);
        });
    },
    truncateTitle(title) {
      return title.length > 50 ? title.substring(0, 48) + "..." : title;
    },
    searchAudios() {
      if (this.search.trim() === "") {
        this.fetchAudios();
      } else {
        this.items = this.items.filter((item) =>
          item.titulo.toLowerCase().includes(this.search.toLowerCase())
        );
      }
    },
    errorMsg(msg) {
      Swal.fire({
        title: "Ops...",
        icon: "error",
        confirmButtonColor: "#91C141",
        html: msg,
      });
    },
    triggerFileInput() {
      const fileInputComponent = this.$refs.fileInput;
      if (fileInputComponent) {
        const input =
          fileInputComponent.$el.querySelector('input[type="file"]');
        if (input) {
          input.click();
        } else {
          console.error("Elemento de input de arquivo não encontrado.");
        }
      } else {
        console.error("Componente v-file-input não encontrado.");
      }
    },
    openEditModal(item) {
      if(this.isTitular){
        this.dialogTitle = "Editar Áudio";
      }else{
        this.dialogTitle = "Visualizar Áudio";
      }
      this.editedItem = Object.assign({}, item);
      this.recordDialog = true;
    },
    openDeleteModal(item) {
      this.deletedItem = item;
      this.deleteDialog = true;
    },
    openRecordAudioDialog() {
      this.recordDialog = true;
      this.editedItem = Object.assign({}, this.defaultItem);
      this.dialogTitle = "Adicionar Áudio";

      // Reseta o estado do áudio
      this.audioBlob = null; // Remove qualquer áudio gravado anterior
      this.audioSrc = ""; // Limpa a fonte de áudio
      this.isPlaying = false;
      this.currentTime = 0;
      this.duration = 0;
      this.currentTimePercentage = 0;
      this.volumePercentage = 100;

      // Se o elemento de áudio já existir, pausá-lo e reiniciá-lo
      if (this.audioElement) {
        this.audioElement.pause();
        this.audioElement.currentTime = 0;
      }

      // Se você estiver gravando, garantir que a gravação também é resetada
      this.isRecording = false;
      this.chunks = [];
      this.canRecord = false;
      this.recorder = null;

      // Preparar para uma nova gravação
      this.setupAudio(); // Configura o microfone para permitir uma nova gravação
    },
    openNewAudioDialog() {
      this.file = null;
      this.uploadDialog = true;
      this.editedItem = Object.assign({}, this.defaultItem);
      this.dialogTitle = "Adicionar Áudio";
    },
    closeDialog() {
      this.uploadDialog = false;
      this.file = null;
    },
    closeRecordDialog() {
      this.recordDialog = false;
    },
    closeDeleteDialog() {
      this.deleteDialog = false;
    },
    closeRecordAudioDialog() {
      this.recordAudioDialog = false;
      this.stopRecording();
      this.audioBlob = null; // Resetar o áudio gravado
    },
    async submitRecordForm() {
      const isValid = await this.$refs.formRecord.validate();

      if (this.dialogTitle == "Editar Áudio" || this.dialogTitle == "Visualizar Áudio") {
        if (!isValid.valid) {
          return;
        }
      } else {
        if (!isValid.valid || this.audioBlob == null) {
          if (this.audioBlob == null) {
            this.snackbarText = "Áudio não encontrado.";
            this.snackbarColor = "error";
            this.snackbarIcon = "mdi-alert-circle";
            this.snackbar = true;
          }
          return;
        }
      }

      if (!this.editedItem.idArquivos && !this.audioBlob) {
        return;
      }

      // Cria um FormData para enviar os dados do formulário e o arquivo
      const formData = new FormData();
      let url;

      // Adiciona o arquivo de áudio
      if (this.editedItem.idArquivos) {
        formData.append("idArquivos", this.editedItem.idArquivos);
        url = this.baseUrl + "audios.php";
      } else {
        formData.append("arquivo", this.audioBlob, "recorded_audio.mp3");
        url = this.baseUrl + "arquivos.php";
      }

      // Adiciona os dados do formulário ao FormData
      formData.append("tituloArquivo", this.editedItem.titulo);
      formData.append("descricaoArquivo", this.editedItem.descricao);

      // Adiciona o ID da pasta (se necessário)
      formData.append("idPasta", this.pasta.id);

      this.disabledButton = true;

      //Faz a requisição POST com multipart/form-data
      axios
        .post(url, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((response) => {
          console.log("Áudio enviado com sucesso!", response.data);
          this.uploadDialog = false;
          this.recordDialog = false;
          this.disabledButton = false;
          this.fetchAudios();
          this.snackbarText = this.editedItem.idArquivos
            ? "Áudio alterado com sucesso"
            : "Áudio criado com sucesso";
          this.snackbarColor = "success";
          this.snackbarIcon = "mdi-check-circle";
          this.snackbar = true;
        })
        .catch((error) => {
          console.error("Erro ao enviar o áudio:", error);
          this.disabledButton = false;
          this.snackbarText = "Erro ao salvar texto";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
        });
    },
    async submitUploadForm() {
      const isValid = await this.$refs.formUpload.validate();

      if (this.dialogTitle == "Editar Áudio" || this.dialogTitle == "Visualizar Áudio") {
        if (!isValid.valid) {
          return;
        }
      } else {
        if (!isValid.valid || this.file == null) {
          if (this.file == null) {
            this.snackbarText = "Pelo menos um arquivo deve ser anexado.";
            this.snackbarColor = "error";
            this.snackbarIcon = "mdi-alert-circle";
            this.snackbar = true;
          }
          return;
        }
      }

      if (!this.editedItem.idArquivos && !this.file) {
        return;
      }

      // Cria um FormData para enviar os dados do formulário e o arquivo
      const formData = new FormData();
      let url;

      // Adiciona o arquivo de áudio
      if (this.editedItem.idArquivos) {
        formData.append("idArquivos", this.editedItem.idArquivos);
        url = this.baseUrl + "audios.php";
      } else {
        formData.append("arquivo", this.file, "upload_audio.mp3");
        url = this.baseUrl + "arquivos.php";
      }

      // Adiciona os dados do formulário ao FormData
      formData.append("tituloArquivo", this.editedItem.titulo);
      formData.append("descricaoArquivo", this.editedItem.descricao);

      // Adiciona o ID da pasta (se necessário)
      formData.append("idPasta", this.pasta.id);

      this.disabledButton = true;

      //Faz a requisição POST com multipart/form-data
      axios
        .post(url, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((response) => {
          console.log("Áudio enviado com sucesso!", response.data);
          this.uploadDialog = false;
          this.recordDialog = false;
          this.disabledButton = false;
          this.fetchAudios();
          this.snackbarText = this.editedItem.idArquivos
            ? "Áudio alterado com sucesso"
            : "Áudio criado com sucesso";
          this.snackbarColor = "success";
          this.snackbarIcon = "mdi-check-circle";
          this.snackbar = true;
        })
        .catch((error) => {
          console.error("Erro ao enviar o áudio:", error);
          this.disabledButton = false;
          this.snackbarText = "Erro ao salvar texto";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
        });
    },
    deleteItemConfirmed(id) {
      axios
        .delete(this.baseUrl + "arquivos.php", {
          params: { idArquivos: id },
        })
        .then((response) => {
          this.closeDeleteDialog();
          this.fetchAudios();
          this.snackbarText = "Áudio excluído com sucesso";
          this.snackbarColor = "success";
          this.snackbarIcon = "mdi-check-circle";
          this.snackbar = true;
        })
        .catch((error) => {
          console.error("Error:", error);
          this.snackbarText = "Erro ao excluir áudio";
          this.snackbarColor = "error";
          this.snackbarIcon = "mdi-alert-circle";
          this.snackbar = true;
        });
    },
    async downloadAudio() {
      const filePath =
        this.baseUrl + this.editedItem.caminho.replace(/\\/g, "/");

      if (filePath) {
        try {
          // Faz uma requisição fetch para buscar o arquivo
          const response = await fetch(filePath);

          // Verifica se a resposta é ok (código 200)
          if (!response.ok) {
            throw new Error("Falha ao buscar o arquivo para download.");
          }

          // Converte a resposta em um blob (arquivo binário)
          const blob = await response.blob();

          // Usa o título do modal como o nome do arquivo
          const fileName = `${this.editedItem.titulo || "download"}.mp3`;

          // Cria um link temporário para o blob
          const link = document.createElement("a");
          link.href = URL.createObjectURL(blob);
          link.setAttribute("download", fileName); // Define o nome do arquivo

          // Adiciona o link ao DOM e clica nele para iniciar o download
          document.body.appendChild(link);
          link.click();

          // Limpa o link do DOM e o URL
          document.body.removeChild(link);
          URL.revokeObjectURL(link.href);
        } catch (error) {
          console.error("Erro ao baixar o arquivo:", error);
        }
      } else {
        console.error("Caminho do arquivo não definido");
      }
    },

    toggleRecord() {
      if (!this.canRecord) {
        this.errorMsg("Permita o uso do microfone para gravar áudio.");
        return;
      }
      if (this.isRecording) {
        this.stopRecording();
      } else {
        this.startRecording();
      }
      if (this.isRecording) {
        this.startTimer();
      } else {
        this.stopTimer();
      }
    },
    startTimer() {
      this.secondsElapsed = 0;
      this.timer = setInterval(() => {
        this.secondsElapsed++;
      }, 1000);
    },
    stopTimer() {
      clearInterval(this.timer);
      this.timer = null;
      this.secondsElapsed = 0;
    },
    setupAudio() {
      navigator.mediaDevices
        .getUserMedia({ audio: true })
        .then(this.setupStream)
        .catch((err) => {
          console.error(err);
          this.errorMsg("Permita o uso do microfone para gravar áudio.");
          this.canRecord = false;
        });
    },
    setupStream(stream) {
      if (stream.active) {
        this.recorder = new MediaRecorder(stream);
        this.recorder.ondataavailable = (e) => this.chunks.push(e.data);
        this.recorder.onstop = this.handleStop;
        this.canRecord = true;
      } else {
        this.errorMessage = "O stream de áudio não está ativo.";
        this.canRecord = false;
      }
    },
    startRecording() {
      if (!this.canRecord) {
        this.errorMsg("Não foi possível iniciar a gravação.");
        return;
      }
      this.chunks = [];
      this.recorder.start();
      this.isRecording = true;
      const btnRecord = document.getElementById("recordAudio");
      btnRecord.classList.replace("mic-off", "mic-on");
    },
    stopRecording() {
      if (this.isRecording) {
        this.recorder.stop();
        this.isRecording = false;
        const btnRecord = document.getElementById("recordAudio");
        btnRecord.classList.replace("mic-on", "mic-off");
      }
    },
    async handleStop() {
      this.audioBlob = new Blob(this.chunks, { type: "audio/mp3" });
      this.chunks = [];

      // Verificar duração do áudio
      const duration = await this.getAudioDuration(this.audioBlob);
      if (duration < 1) {
        this.errorMsg("O áudio deve ter pelo menos 1 segundo.");
        this.audioBlob = null;
        return;
      }
    },
    getAudioDuration(blob) {
      return new Promise((resolve, reject) => {
        const audioContext = new (window.AudioContext ||
          window.webkitAudioContext)();
        const reader = new FileReader();
        reader.onload = () => {
          audioContext.decodeAudioData(
            reader.result,
            (buffer) => {
              resolve(buffer.duration);
            },
            (err) => reject(err)
          );
        };
        reader.onerror = (err) => reject(err);
        reader.readAsArrayBuffer(blob);
      });
    },
    playAudioTable(item) {
      console.log("teste", item);
      if (!this.audioElementDatatable) {
        this.audioElementDatatable = this.$refs.audioElementDatatable;
      }
      if (this.isPlayingOnDatatable) {
        this.audioElementDatatable.pause();
        this.isPlayingOnDatatable = false;
      } else {
        this.audioElementDatatable.src = this.baseUrl + item.caminho;

        this.audioElementDatatable.addEventListener(
          "canplaythrough",
          () => {
            this.audioElementDatatable.play();
            this.isPlayingOnDatatable = true;
            this.playItem = item.idArquivos;
          },
          { once: true }
        );
      }
    },
    onAudioEndedTable() {
      // Reinicia o estado quando o áudio terminar
      this.isPlayingOnDatatable = false;
      this.playItem = null;
    },
    playAudio() {
      if (this.audioElement) {
        this.audioElement.play();
        this.isPlaying = true;
      }
    },
    pauseAudio() {
      if (this.audioElement) {
        this.audioElement.pause();
        this.isPlaying = false;
      }
    },
    togglePlayPause() {
      if (this.isPlaying) {
        this.pauseAudio();
      } else {
        this.playAudio();
      }
    },
    seekAudio() {
      if (this.audioElement) {
        const seekTime = (this.currentTimePercentage / 100) * this.duration;
        this.audioElement.currentTime = seekTime;
      }
    },
    toggleMute() {
      if (this.isMuted) {
        // Se estiver mudo, restaura o volume anterior
        this.volumePercentage = this.previousVolume;
        this.adjustVolume();
      } else {
        // Se não estiver mudo, armazena o volume atual e coloca o volume em 0
        this.previousVolume = this.volumePercentage;
        this.volumePercentage = 0;
        this.adjustVolume();
      }
      this.isMuted = !this.isMuted;
    },
    adjustVolume() {
      if (this.audioElement) {
        this.audioElement.volume = this.volumePercentage / 100;
      }
    },
    updateTime() {
      if (this.audioElement) {
        this.currentTime = this.audioElement.currentTime;
        this.duration = this.audioElement.duration;
        this.currentTimePercentage = (this.currentTime / this.duration) * 100;
      }
    },
    onAudioEnded() {
      this.isPlaying = false;
    },
    formatTime(seconds) {
      if (!isNaN(seconds) && seconds !== Infinity) {
        const min = Math.floor(seconds / 60);
        const sec = Math.floor(seconds % 60);
        return `${String(min).padStart(2, "0")}:${String(sec).padStart(
          2,
          "0"
        )}`;
      }
      return "00:00";
    },
    async fetchAudios() {
      try {
        const response = await axios.get(this.baseUrl + "arquivos.php", {
          params: {
            idPasta: this.pasta.id,
          },
        });
        console.log(response.data);
        this.items = response.data;
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
        const response = await axios.get(this.baseUrl + "pastas.php", {
          params: {
            idPasta: this.pasta.id,
          },
        });
        console.log("response", response);
        const jsonString = response.data.match(/\[.*\]/)[0];
        this.pasta.titulo = JSON.parse(jsonString)[0].titulo;
      } catch (error) {
        console.error("Error:", error);
      }
    },
  },
  mounted() {
    this.fetchAudios();
    this.fetchFolder();
  },
};
</script>

<style scoped>
.title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 99.9%;
}

.v-dialog {
  z-index: 1060 !important;
}

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
  width: 100px;
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

.v-row {
  margin: 0;
}

.btn-novo-audio {
  text-align: center;
  padding: 0 10px;
  background-color: #91c141;
  color: #fff;
}

.btn-novo-audio:hover {
  background-color: #6d9232;
}

.btn-gravar-audio {
  text-align: center;
  padding: 0 10px;
  background-color: #617a95;
  color: #fff;
}

.btn-gravar-audio:hover {
  background-color: #4d6279;
}

.mic-off {
  background-color: #617a95;
  color: white;
  transition: all 0.25s;
}

.mic-on {
  background-color: red;
  color: white;
}

.audio-player {
  margin: 0 auto;
  display: flex;
  align-items: center;
  gap: 10px;
  background-color: rgba(0, 0, 0, 0.1);
  padding: 10px;
  border-radius: 25px;
  width: fit-content;
}

.audio-player button {
  background: none;
  border: none;
  font-size: 16px;
  cursor: pointer;
  background-color: white;
}

.iconPlayer {
  color: #91c141;
}

.iconVolume {
  color: #617a95;
}

.barPlayer {
  accent-color: #91c141;
  min-width: 80px !important;
  max-width: 100px;
}

.timePlayer {
  color: #252526;
}

.volumePlayer {
  accent-color: #617a95;
  width: 50px;
}

.audio-player input[type="range"] {
  flex: 1;
}

.hidden {
  display: none;
}

.bottons {
  margin-top: 16px;
  align-self: flex-end;
}

.cronometro {
  color: #617a95;
  font-size: 18px;
  margin-bottom: 5px;
  display: block;
  text-align: center;
}
.download {
  background-color: #91c141;
  color: #fff;
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

  .bottons {
    align-self: center;
  }
}
</style>
