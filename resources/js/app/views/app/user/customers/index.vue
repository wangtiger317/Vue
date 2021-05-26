<template>
  <data-table
    model="App\Customer"
  >
    <template v-slot:empty-head>
      <v-icon size="72" :color="$store.getters.app.color_name">contacts</v-icon>
    </template>

    <template v-slot:empty-text>
      <p class="subheading">Os clientes vão aparecer aqui quando se registarem no site da sua
          campanha. Pode também fazer o registo de um cliente usando a área de funcionário. Encontra os
          endereços do site da sua campanha e área de funcionário em <router-link :to="{name: 'user.campaigns'}">campanhas</router-link>.</p>
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
