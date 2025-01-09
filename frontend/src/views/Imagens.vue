<template>
    <div class="wrapper">
        <Navbar />
            <TableImageFile />              
        <Footer />
    </div>
</template>

<script>
import Navbar from '../components/gerais/Navbar.vue'
import Footer from '../components/gerais/Footer.vue'
import TableImageFile from '../components/imageFile/TableImageFile.vue'

export default {
    name: "Imagefile",
    components: {
        Navbar,
        Footer,
        TableImageFile
     }
    
}
</script>

<style scoped>
    .wrapper{
        margin-bottom: 200px
    }
</style>