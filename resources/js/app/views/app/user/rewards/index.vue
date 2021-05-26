<template>
  <data-table
    model="Platform\Models\Reward"
  >
    <template v-slot:empty-head>
      <v-icon size="72" :color="$store.getters.app.color_name">fas fa-gift</v-icon>
    </template>

    <template v-slot:empty-text>
      <p class="subheading">Os seus clientes podem usar os pontos ganhos para trocar por prémios. Um prémio pode ser usado em uma ou mais campanhas.</p>
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
