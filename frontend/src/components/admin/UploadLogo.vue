<template>
  <div style="height: 100%;">
    <div class="container" style="height: 100%;">
      <div
        v-if="imageUrls.length > 0"
        class="image-container"
        @mouseover="hoveredIndex = 0"
        @mouseleave="hoveredIndex = -1"
        style="justify-content: center; display: flex; flex-direction: column;"
      >
        <div class="image-wrapper">
          <img
            :src="imageUrls[0]"
            width="150"
            v-if="isImage(0)"
            @click="openDocument(0)"
            style="cursor: pointer;"
          />
          <div
            class="overlay"
            v-show="hoveredIndex === 0"
            @click="removeFile(0)"
          >
            <v-icon small class="delete-icon">mdi-close</v-icon>
          </div>
        </div>
        <p class="name">{{ truncateName(imageNames[0]) }}</p>
      </div>
    </div>
    <v-container>
      <v-row class="btn-upload-wrapper pb-2">
        <v-btn text elevation="0" rounded="0" @click="pickFile"
          ><span class="mdi mdi-camera"></span>{{ buttonText }}</v-btn
        >
        <input
          type="file"
          ref="image"
          :accept="acceptedFileTypes"
          @change="onFilePicked"
          style="display: none"
        />
      </v-row>
    </v-container>
  </div>
</template>

<script>
export default {
  props: {
    logo: {
      type: String,
      default: "",
    },
    buttonText: {
      type: String,
      default: "ADICIONAR LOGO",
    },
  },
  data() {
    return {
      imageUrls: [],
      imageFiles: [],
      imageNames: [],
      hoveredIndex: -1,
      baseUrl: "http://localhost:8181/",
    };
  },
  computed: {
    acceptedFileTypes() {
      // Verifica se buttonText é 'ADICIONAR FAVICON' e altera o tipo de arquivo aceito
      if (this.buttonText === 'ADICIONAR FAVICON') {
        return '.ico'; // Aceita apenas arquivos .ico
      }
      return 'image/jpeg, image/jpg, image/png'; // Aceita imagens padrão
    },
  },
  methods: {
    getImageFiles() {
      return this.imageFiles;
    },
    async pickFile() {
      this.$refs.image.click();
    },
    async onFilePicked(e) {
      const file = e.target.files[0];

      if (!file) {
        return;
      }

      if (this.validateFile(file)) {
        this.imageUrls = [];
        this.imageFiles = [];
        this.imageNames = [];

        const fr = new FileReader();
        fr.onload = (event) => {
          const url = event.target.result;
          this.imageUrls.push(url);
          this.imageFiles.push(file);
          this.imageNames.push(file.name);
        };
        fr.readAsDataURL(file);
      }
    },
    removeFile(index) {
      this.imageUrls = [];
      this.imageFiles = [];
      this.imageNames = [];
    },
    isImage(index) {
      const file = this.imageFiles[index];
      return file.type.startsWith("image/");
    },
    openDocument(index) {
      const file = this.imageFiles[index];
      const fileURL = URL.createObjectURL(file);
      window.open(fileURL, "_blank");
    },
    truncateName(name) {
      return name.length > 25 ? name.substring(0, 23) + "..." : name;
    },
    validateFile(file) {
      const allowedFileTypes = ["image/jpeg", "image/jpg", "image/png"];
      const maxFileSize = 25 * 1024 * 1024; // Tamanho máximo do arquivo em bytes (25 MB)

      if (this.buttonText === 'ADICIONAR FAVICON') {
        // Se for para adicionar um favicon, somente arquivos .ico são permitidos
        if (file.type !== 'image/x-icon') {
          alert("Somente arquivos .ico são permitidos.");
          return false;
        }
      } else {
        // Se for para adicionar uma imagem, valida os tipos de imagem permitidos
        if (!allowedFileTypes.includes(file.type)) {
          alert(`O tipo de arquivo ${file.type} não é permitido.`);
          return false;
        }
      }

      if (file.size > maxFileSize) {
        alert(`O arquivo ${file.name} é muito grande. O tamanho máximo permitido é de ${maxFileSize / (1024 * 1024)} MB.`);
        return false;
      }

      return true;
    },
  },
  watch: {
    // Observar alterações na prop 'logo'
    logo(newLogo) {
      const fileType = newLogo.split('.').pop().toLowerCase();
      const allowedTypes = ["jpeg", "jpg", "png"];
      
      if (this.buttonText === 'ADICIONAR FAVICON' && newLogo.split('.').pop().toLowerCase() === 'ico') {
        // Se for para favicon e o logo for do tipo .ico
        this.imageUrls.push(this.baseUrl + newLogo);
        this.imageFiles.push({
          name: newLogo.split("/").pop(),
          type: 'image/x-icon',
        });
        this.imageNames.push(newLogo.split("/").pop());
      } else if (allowedTypes.includes(fileType)) {
        // Caso seja uma imagem válida (jpeg, jpg, png)
        this.imageUrls.push(this.baseUrl + newLogo);
        this.imageFiles.push({
          name: newLogo.split("/").pop(),
          type: `image/${fileType}`,
        });
        this.imageNames.push(newLogo.split("/").pop());
      }
      console.log(newLogo);
    },
  },
};
</script>

<style scoped>
.btn-upload-wrapper {
  justify-content: center;
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
.overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  opacity: 0;
  transition: opacity 0.3s;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
}
.overlay:hover {
  opacity: 1;
}
.delete-icon {
  color: white;
  position: absolute;
  top: 0;
  right: 0px;
}
.container {
  display: flex;
  padding-top: 16px;
  justify-content: center;
}
</style>
