<template>
  <v-app>
    <v-main>
      <RouterView />
    </v-main>
  </v-app>
</template>

<script>
import { checkConnection } from "./components/Apis/apisService";

export default {
  name: "App",
  components: {},
  mounted() {
    this.checkConnectionBackend();
  },
  methods: {
    async checkConnectionBackend() {
      try {
        const result = await checkConnection();
        console.log("Conexão:", result);
      } catch (error) {
        console.error("Erro ao conectar ao backend:", error.message);
      }
    },
  },
};
</script>

<style>
@import url("https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900&display=swap");

* {
  font-family: Roboto, sans-serif;
}

html {
  box-sizing: border-box;
  height: 100%;
}

body {
  position: relative;
  margin: 0;
  min-height: 100%;
  box-sizing: inherit;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
</style>
