<template>
 <div class="wrapper">
    <Navbar />
      <TableDocumentFile />   
    <Footer />
  </div>
</template>

<script>
import Navbar from '../components/gerais/Navbar.vue'
import Footer from '../components/gerais/Footer.vue'
import TableDocumentFile from '../components/documentFile/TableDocumentFile.vue'


export default {
    name: "DocumentFile",
    components: {
        Navbar,
        Footer,
        TableDocumentFile
     }
    
}
</script>

<style scoped>
.wrapper{
  margin-bottom: 200px;
}
</style>