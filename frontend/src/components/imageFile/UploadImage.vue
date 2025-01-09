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
              accept="image/jpeg, image/jpg, image/png"
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
              @click="downloadImages"
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
          <img
            class="image"
            :src="url"
            height="100"
            width="100"
            cover
            style="object-fit: cover"
            @click="openImage(url)"
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
    titulo: {
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

      if (this.imageFiles.length + files.length > 10) {
        alert("Você pode anexar no máximo 10 arquivos.");
        return;
      }

      for (const file of files) {
        if (this.validateFile(file)) {
          const existingIndex = this.imageFiles.findIndex(
            (existingFile) =>
              existingFile.name === file.name &&
              existingFile.size === file.size &&
              existingFile.type === file.type
          );

          const url = URL.createObjectURL(file); // Cria uma URL temporária

          if (existingIndex !== -1) {
            // Atualiza o arquivo existente
            this.imageUrls.splice(existingIndex, 1, url);
            this.imageFiles.splice(existingIndex, 1, file);
            this.imageNames.splice(existingIndex, 1, file.name);
          } else {
            // Adiciona um novo arquivo
            this.imageUrls.push(url);
            this.imageFiles.push(file);
            this.imageNames.push(file.name);
          }
        }
      }
    },
    openImage(url) {
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
      return name.length > 16 ? name.substring(0, 14) + "..." : name;
    },
    validateFile(file) {
      const allowedFileTypes = ["image/jpeg", "image/jpg", "image/png"];
      const maxFileSize = 500 * 1024 * 1024; // Tamanho máximo do arquivo em bytes (500 MB)

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
    getImageFiles() {
      return this.imageFiles;
    },
    async downloadImages() {
      if (!this.imageFiles.length) {
        alert("Nenhum arquivo para baixar.");
        return;
      }

      const zip = new JSZip();
      let fileCount = 0;

      for (const [index, file] of this.imageFiles.entries()) {
        if (!file) {
          console.error(
            `O arquivo na posição ${index} está vazio ou indefinido.`
          );
          continue;
        }

        let fileData;
        let fileName = file.name || `arquivo-${index + 1}`;

        if (file instanceof File || file instanceof Blob) {
          // Se é um arquivo local (File/Blob)
          fileData = file;
          zip.file(fileName, fileData);
          fileCount++;
        } else if (typeof file.url === "string") {
          // Se é uma URL remota, faça o fetch para pegar o conteúdo
          try {
            const response = await fetch(this.baseUrl + file.url); // Confirme o URL completo
            if (response.ok) {
              fileData = await response.blob();
              zip.file(fileName, fileData);
              fileCount++;
            } else {
              console.error(`Erro ao carregar a URL: ${file.url}`);
            }
          } catch (error) {
            console.error(
              `Erro ao carregar o arquivo ${fileName} a partir de URL:`,
              error
            );
          }
        } else {
          console.error("Formato de arquivo não suportado", file);
        }
      }

      console.log("Total de arquivos adicionados ao ZIP:", fileCount);

      if (fileCount > 0) {
        const zipContent = await zip.generateAsync({ type: "blob" });
        saveAs(zipContent, `${this.titulo || "arquivos"}.zip`);
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
        const completeUrl = this.baseUrl + url;
        this.imageUrls.push(completeUrl);

        // Adiciona a URL no formato correto
        this.imageFiles.push({ name: url.split("/").pop(), url });
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
.image {
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
