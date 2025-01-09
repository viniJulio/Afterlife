<template>
    <div class="wrapper">
        <Navbar />
        <DatatableAdmin :showModalEdit="showModalEdit"/>
        <Footer />
    </div>
</template>

<script>
import Navbar from "../components/gerais/Navbar.vue";
import Footer from "../components/gerais/Footer.vue";
import DatatableAdmin from "../components/admin/DatatableUsers.vue";

export default {
    name: 'viewAdmin',
    components:{Navbar,Footer, DatatableAdmin},
    data(){
        return{
            showModalEdit: false,
        };
    },
};
</script>

<style scoped>
.wrapper{
  margin-bottom: 200px;
}
</style>