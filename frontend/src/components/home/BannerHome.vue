<template>
  <div class="banner d-flex flex-column-reverse flex-md-row justify-center align-center ga-6">
    <div class="text text-center text-md-right">
      <h1 class="pb-2">Segurança para suas informações</h1>
      <h3 class="pb-3">
        Gerenciamento seguro de dados pessoais, garantindo tranquilidade para o amanhã.
      </h3>
      <v-btn rounded="0" class="btn-registrar" @click="$emit('open-register')">CRIAR CONTA</v-btn>
    </div>
    <div class="d-flex justify-center">
      <img class="image" src="../../assets/imgs/ComputadorComEscudo.png" alt="computador">
    </div>
  </div>
</template>

<script>
export default {
  name: 'BannerHome'
};
</script>

<style scoped>
.banner {
  width: 100%;
  background-color: #F1F1F1;
  padding: 45px 0;
}
.text {
  max-width: 500px;
  width: 90%;
}
.image {
  max-width: 90%;
}
.btn-registrar {
  background-color: #91C141 !important;
  color: white !important;
  padding: 5px 10px;
}
.btn-registrar:hover {
  background-color: #6d9232 !important;
}
h3 {
  font-weight: 400;
  font-style: italic;
}

@media screen and (max-width: 767px) {
h3 {
  font-style: normal;
}
}
</style>
