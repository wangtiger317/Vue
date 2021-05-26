<template>
  <data-table
    model="Platform\Models\Business"
  >
    <template v-slot:empty-head>
      <v-icon size="72" :color="$store.getters.app.color_name">business</v-icon>
    </template>

    <template v-slot:empty-text>
      <p class="subheading">O estabelecimento é a entidade onde irá associar Funcionários e Campanhas de Fidelização.</p>
      <p class="subheading">Preencha com as informações relevantes para os seus clientes. As informações introduzidas serão usadas no site da sua campanha.</p>
    </template>

  </data-table>
</template>
<script>
  export default {
    data() {
      return {
      }
    },
    methods: {
    }
  }
</script>
