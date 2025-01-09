<template>
  <div>
    <v-container>
      <v-row class="btn-upload-wrapper">
        <v-tooltip text="Escolher arquivo" location="bottom" v-if="isTitular">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon color="#617A95" @click="pickFile">
              <v-icon>mdi-paperclip</v-icon>
            </v-btn>
            <input
              type="file"
              ref="image"
              accept="application/pdf, application/msword, application/vnd.ms-excel, application/docx, application/odt, application/rtf, application/txt, application/html, application/epub, application/md"
              @change="onFilePicked"
              style="display: none"
              multiple
            />
          </template>
        </v-tooltip>
        <v-tooltip text="Baixar arquivos" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn
              v-bind="props"
              icon="mdi-tray-arrow-down"
              id="recordAudio"
              class="download"
              @click="downloadDocumentos"
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
            :src="resolveImageUrl(index)"
            height="100"
            width="100"
            @click="openDocument(index)"
            v-if="isImage(index) || isFileIcon(index)"
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
          <!-- <div class="overlay"  @click.stop="openDocument(index)" v-show="hoveredIndex === index" > -->
          <!-- </div> -->
        </div>
        <p class="name">{{ truncateName(imageNames[index]) }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import pdfIcon from "@/assets/imgs/pdficon.png";
import wordIcon from "@/assets/imgs/wordicon.png";
import excelIcon from "@/assets/imgs/excelicon.png";
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
  mounted() {
    this.checkTitular();
    if (this.caminho) {
      // Divida a string de caminho usando vírgulas como delimitadores
      const filePaths = this.caminho.split(",");

      filePaths.forEach((filePath) => {
        const url = this.baseUrl + filePath.trim();
        const fileName = filePath.split("/").pop(); // Extrair o nome do arquivo do caminho

        // Adicionar à lista de arquivos para exibição
        this.imageUrls.push(url);
        this.imageNames.push(fileName);

        // Aqui estamos criando um arquivo "fictício" para manter consistência
        this.imageFiles.push({
          name: fileName,
          type: this.getFileType(fileName),
          url: url, // Esse campo pode ser usado para abrir o arquivo diretamente
        });
      });
    }
  },
  data() {
    return {
      files: [],
      imageUrls: [],
      imageFiles: [],
      imageNames: [],
      hoveredIndex: -1,
      pdfIcon: pdfIcon,
      wordIcon: wordIcon,
      excelIcon: excelIcon,
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
    getFileType(fileName) {
      const extension = fileName.split(".").pop().toLowerCase();
      switch (extension) {
        case "pdf":
          return "application/pdf";
        case "doc":
        case "docx":
          return "application/msword";
        case "xls":
        case "xlsx":
          return "application/vnd.ms-excel";
        case "mp4":
        case "avi":
        case "mpeg":
        case "mov":
        case "webm":
        case "mkv":
          return `video/${extension}`;
        default:
          return "unknown"; // Caso precise lidar com outros tipos
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

          if (existingIndex !== -1) {
            const fr = new FileReader();
            fr.onload = (event) => {
              const url = event.target.result;
              this.imageUrls.splice(existingIndex, 1, url);
              this.imageFiles.splice(existingIndex, 1, file);
              this.imageNames.splice(existingIndex, 1, file.name);
            };
            fr.readAsDataURL(file);
          } else {
            const fr = new FileReader();
            fr.onload = (event) => {
              const url = event.target.result;
              this.imageUrls.push(url);
              this.imageFiles.push(file);
              this.imageNames.push(file.name);
            };
            fr.readAsDataURL(file);
          }
        }
      }
    },
    removeFile(url, index) {
      this.imageUrls.splice(index, 1);
      this.imageFiles.splice(index, 1);
      this.imageNames.splice(index, 1);

      const cleanUrl = url.replace(this.baseUrl, "");
      this.deleteUrls.push(cleanUrl);

      this.$emit("update-deleted-urls", this.deleteUrls); // Emite o array atualizado
    },
    isImage(index) {
      const file = this.imageFiles[index];
      return file.type.startsWith("image/");
    },
    isFileIcon(index) {
      const file = this.imageFiles[index];
      return (
        file.type === "application/pdf" ||
        file.type === "application/msword" ||
        file.type === "application/vnd.ms-excel"
      );
    },
    openDocument(index) {
      // debugger;
      const file = this.imageFiles[index];

      // Verificar se file é um objeto File válido
      if (file && file instanceof File) {
        const fileURL = URL.createObjectURL(file);
        window.open(fileURL, "_blank");
      } else {
        const caminhosArray = this.caminho.split(",");
        const fileURL = `http://localhost:8181/${caminhosArray[index]}`;
        window.open(fileURL, "_blank");
        // console.error("Arquivo não encontrado ou não é um objeto File válido.");
      }
    },
    truncateName(name) {
      return name.length > 16 ? name.substring(0, 14) + "..." : name;
    },
    validateFile(file) {
      const allowedFileTypes = [
        "application/pdf",
        "application/msword",
        "application/vnd.ms-excel",
        "application/docx",
        "application/odt",
        "application/rtf",
        "application/txt",
        "application/html",
        "application/epub",
        "application/md",
      ];
      const maxFileSize = 10 * 1024 * 1024; // Tamanho máximo do arquivo em bytes (10 MB)

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
    getDocumentFiles() {
      return this.imageFiles;
    },
    resolveImageUrl(index) {
      const file = this.imageFiles[index];
      if (file.type === "application/pdf") {
        return this.pdfIcon;
      } else if (file.type === "application/msword") {
        return this.wordIcon;
      } else if (file.type === "application/vnd.ms-excel") {
        return this.excelIcon;
      }
      return this.imageUrls[index];
    },
    async downloadDocumentos() {
      if (this.imageFiles.length === 0) {
        alert("Nenhum arquivo para baixar.");
        return;
      }

      const zip = new JSZip();
      let fileCount = 0;

      for (const [index, file] of this.imageFiles.entries()) {
        let fileData;
        let fileName = file.name || `arquivo-${index + 1}`;

        if (file instanceof File || file instanceof Blob) {
          fileData = file;
          zip.file(fileName, fileData);
          fileCount++;
        } else if (typeof file.url === "string") {
          try {
            const response = await fetch(file.url);
            if (response.ok) {
              fileData = await response.blob();
              zip.file(fileName, fileData);
              fileCount++;
            }
          } catch (error) {
            console.error(
              `Erro ao carregar o arquivo ${fileName} a partir de URL:`,
              error
            );
          }
        }
      }

      if (fileCount > 0) {
        const zipContent = await zip.generateAsync({ type: "blob" });
        saveAs(zipContent, `${this.titulo}.zip`);
      } else {
        alert(
          "Nenhum arquivo foi adicionado ao ZIP. Verifique se os arquivos são válidos."
        );
      }
    },
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
.name {
  font-size: 12px;
}
.image-wrapper:hover .overlay {
  opacity: 1;
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
