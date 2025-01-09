<template>
  <div>
    <v-container>
      <v-row class="btn-upload-wrapper">
        <v-tooltip text="Escolher arquivo" location="bottom" v-if="isTitular">
          <template v-slot:activator="{ props }">
            <v-btn
              v-bind="props"
              @click="pickFile"
              icon="mdi-paperclip"
              color="#617A95"
            ></v-btn>
            <v-file-input
              ref="image"
              accept="video/mp4, video/avi, video/wmv, video/avchd"
              @change="onFilePicked"
              style="display: none"
              hide-input
              multiple
            ></v-file-input>
          </template>
        </v-tooltip>
        <v-tooltip text="Baixar arquivos" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn
              v-bind="props"
              icon="mdi-tray-arrow-down"
              id="recordAudio"
              class="download"
              @click="downloadVideos"
            ></v-btn>
          </template>
        </v-tooltip>
      </v-row>
    </v-container>

    <div class="container">
      <div
        v-for="(url, index) in imageUrls"
        :key="index"
        class="image-container"
        @mouseover="hoveredIndex = index"
        @mouseleave="hoveredIndex = -1"
      >
        <div class="image-wrapper">
          <video
            class="video"
            :src="url"
            height="100"
            width="100"
            @click="openVideo(url)"
            cover
            style="object-fit: cover"
          />
          <v-icon
            v-if="isTitular"
            small
            class="delete-icon"
            @click="removeFile(url, index)"
            v-show="hoveredIndex === index"
            >mdi-delete</v-icon
          >
        </div>
        <p class="name">{{ truncateName(imageNames[index]) }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import JSZip from "jszip";
import { saveAs } from "file-saver";
import axios from "axios";

export default {
  props: {
    caminho: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      imageUrls: [],
      imageFiles: [],
      imageNames: [],
      hoveredIndex: -1,
      baseUrl: "http://localhost:8181/",
      deleteUrls: [],
      isTitular: null,
    };
  },
  methods: {
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
    async pickFile() {
      this.$refs.image.click();
    },
    async onFilePicked(e) {
      const files = Array.from(e.target.files);
      const newVideos = [];

      if (this.imageFiles.length + files.length > 10) {
        alert("Você pode anexar no máximo 10 arquivos.");
        return;
      }

      for (const file of files) {
        if (this.imageNames.includes(file.name)) {
          alert(`O arquivo ${file.name} já foi carregado.`);
          continue;
        }
        if (this.validateFile(file)) {
          const url = URL.createObjectURL(file); // Cria uma URL temporária para o arquivo
          this.imageUrls.push(url);
          this.imageFiles.push(file);
          this.imageNames.push(file.name);

          newVideos.push({ url, name: file.name, file });

          if (newVideos.length === files.length) {
            this.$emit("videos-added", newVideos);
          }
        }
      }
      this.$refs.image.value = "";
    },
    openVideo(url) {
      if (url) {
        const link = document.createElement("a");
        link.href = url;
        link.target = "_blank";
        link.download = "";

        // Adiciona o link ao DOM e clica nele para iniciar o download
        document.body.appendChild(link);
        link.click();

        // Remove o link do DOM
        document.body.removeChild(link);
      } else {
        console.error("Caminho do arquivo não definido");
      }
    },
    removeFile(url, index) {
      // Libera a URL temporária antes de remover
      URL.revokeObjectURL(this.imageUrls[index]);
      this.imageUrls.splice(index, 1);
      this.imageFiles.splice(index, 1);
      this.imageNames.splice(index, 1);

      const cleanUrl = url.replace(this.baseUrl, "");
      this.deleteUrls.push(cleanUrl);

      this.$emit("update-deleted-urls", this.deleteUrls); // Emite o array atualizado
    },
    openDocument(index) {
      const file = this.imageFiles[index];
      const fileURL = URL.createObjectURL(file);
      window.open(fileURL, "_blank");
    },
    truncateName(name) {
      return name.length > 17 ? name.substring(0, 15) + "..." : name;
    },
    validateFile(file) {
      const allowedFileTypes = [
        "video/mp4",
        "video/avi",
        "video/wmv",
        "video/avchd",
      ];
      const maxFileSize = 500 * 1024 * 1024; // Tamanho máximo do arquivo em bytes (100 MB)
      if (!allowedFileTypes.includes(file.type)) {
        alert(`O tipo de arquivo ${file.type} não é permitido.`);
        return false;
      }
      if (file.size > maxFileSize) {
        alert(
          `O arquivo ${
            file.name
          } é muito grande. O tamanho máximo permitido é de ${
            maxFileSize / (1024 * 1024)
          } MB.`
        );
        return false;
      }
      return true;
    },
    getVideoFiles() {
      return this.imageFiles;
    },
    async downloadVideos() {
      if (this.imageFiles.length === 0) {
        alert("Nenhum arquivo para baixar.");
        return;
      }

      const zip = new JSZip();
      let fileCount = 0;

      // Função auxiliar para acessar o arquivo real no Proxy
      const getFileData = async (file, index) => {
        // Verifica se o arquivo é um Proxy e acessa o objeto original
        const realFile = file?.__v_raw || file;

        // Log para inspecionar a estrutura de realFile
        console.log("realFile:", realFile);

        // Se for um arquivo válido (File ou Blob)
        if (realFile instanceof File || realFile instanceof Blob) {
          return realFile; // Retorna o arquivo diretamente
        }

        // Se o arquivo tem nome, tentamos verificar se está no diretório local
        if (realFile && realFile.name) {
          const fileName = realFile.name; // Obter o nome do arquivo
          const localUrl = `http://localhost:8181/assets/arquivos/videos/${fileName}`;

          try {
            // Verificar se o vídeo está disponível localmente
            const localResponse = await fetch(localUrl, { method: "HEAD" });

            if (localResponse.ok) {
              // Se a URL local estiver acessível, retorna o arquivo diretamente
              console.log(`Vídeo encontrado localmente: ${fileName}`);
              return fetch(localUrl).then((res) => res.blob()); // Retorna o Blob do vídeo
            } else {
              console.log(
                `Vídeo não encontrado localmente, tentando buscar pela URL remota.`
              );
            }
          } catch (error) {
            console.error(`Erro ao verificar a URL local: ${localUrl}`, error);
          }
        }

        // Se for uma URL (caso esteja usando URLs como base para download)
        else if (realFile && typeof realFile.url === "string") {
          try {
            const response = await fetch(realFile.url);
            if (response.ok) {
              return await response.blob(); // Converte a resposta em um Blob
            } else {
              console.error(`Erro ao carregar a URL: ${realFile.url}`);
              return null;
            }
          } catch (error) {
            console.error(
              `Erro ao buscar arquivo de URL: ${realFile.url}`,
              error
            );
            return null;
          }
        } else {
          console.error(`Arquivo ${index} não é suportado:`, realFile);
          return null;
        }
      };

      // Processa todos os arquivos
      for (const [index, file] of this.imageFiles.entries()) {
        const fileData = await getFileData(file, index);
        const fileName = file.name || `video-${index + 1}.mp4`;

        if (fileData) {
          zip.file(fileName, fileData); // Adiciona o arquivo ao ZIP
          fileCount++;
        }
      }

      // Se arquivos foram adicionados, gera o ZIP
      if (fileCount > 0) {
        const zipContent = await zip.generateAsync({ type: "blob" });
        const zipName = `${this.titulo || "videos"}.zip`;
        saveAs(zipContent, zipName);
      } else {
        alert(
          "Nenhum arquivo foi adicionado ao ZIP. Verifique se os arquivos são válidos."
        );
      }
    },
  },
  mounted() {
    this.checkTitular();
    if (this.caminho) {
      const urls = this.caminho.split(",").map((url) => url.trim());

      for (const url of urls) {
        this.imageUrls.push(this.baseUrl + url);
        this.imageFiles.push({
          name: url.split("/").pop(),
          type: "video/mp4",
        });
        this.imageNames.push(url.split("/").pop());
      }
    }
  },
};
</script>

<style scoped>
.btn-upload-wrapper {
  justify-content: center;
  gap: 15px;
}
.image-container {
  position: relative;
  display: inline-block;
  margin: 5px;
  text-align: center;
}
.image-wrapper {
  position: relative;
  display: inline-block;
}
.video {
  background-color: #f1f1f1;
  cursor: pointer;
}
.name {
  font-size: 12px;
}
.delete-icon {
  color: white;
  position: absolute;
  top: 0;
  right: 0px;
  background-color: rgba(0, 0, 0, 0.5);
  transition: opacity 0.3s;
  cursor: pointer;
}
.container {
  display: flex;
  flex-wrap: wrap;
  padding-top: 16px;
  justify-content: center;
  column-gap: 10px;
}
.download {
  background-color: #91c141;
  color: #fff;
}
</style>
