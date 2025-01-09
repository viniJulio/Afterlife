<template>
  <v-card class="card" elevation="0">
    <v-tabs v-model="tab" align-tabs="center" color="black">
      <v-tab :value="1">Empresa</v-tab>
      <v-tab :value="2">Usuários</v-tab>
    </v-tabs>

    <v-tabs-window v-model="tab" class="pt-1">
      <v-tabs-window-item :value="1">
        <DatatableBusiness />
      </v-tabs-window-item>
      <v-tabs-window-item :value="2">
        <DatatableUsers />
      </v-tabs-window-item>
    </v-tabs-window>
  </v-card>
</template>

<script>
import DatatableBusiness from "../admin/DatatableBusiness.vue";
import DatatableUsers from "../admin/DatatableUsers.vue";

export default {
  data: () => ({
    tab: 1,
  }),
  components: {
    DatatableBusiness,
    DatatableUsers,
  },
};
</script>

<style scoped>
.card {
  height: calc(100vh - 100px);
  height: 100%;
}
</style>
