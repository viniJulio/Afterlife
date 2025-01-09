<template>
    <div class="wrapper">
        <Navbar />
            <Pastas/>
        <Footer />
    </div>
</template>

<script>
import Navbar from "../components/gerais/Navbar.vue";
import Footer from "../components/gerais/Footer.vue";
import Pastas from "../components/pastas/Pastas.vue";

export default {
    name: 'viewFolders',
    components:{Navbar, Footer, Pastas},
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