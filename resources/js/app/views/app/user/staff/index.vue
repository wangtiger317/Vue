<template>
  <data-table
    model="App\Staff"
  >
    <template v-slot:empty-head>
      <v-icon size="72" :color="$store.getters.app.color_name">work</v-icon>
    </template>

    <template v-slot:empty-text>
      <p class="subheading">Os funcionários estão associados aos estabelecimentos. Terão acesso à lista de clientes, podendo adicionar pontos e atribuir prémios.</p>
      <p class="subheading">Os funcionários têm acesso aos dados do estabelecimento aos quais estão associados.</p>
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
