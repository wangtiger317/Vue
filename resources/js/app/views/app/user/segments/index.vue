<template>
  <data-table
    model="Platform\Models\Segment"
  >
    <template v-slot:empty-head>
      <v-icon size="72" :color="$store.getters.app.color_name">category</v-icon>
    </template>

    <template v-slot:empty-text>
      <p class="subheading">Os segmentos permitem organizar os seus clientes aquando da atribuição de pontos ou prémios.</p>
      <p class="subheading">Por exemplo, pode criar segmentos como "masculino", "feminino", ou "idade 25-34". O uso de segmentos é opcional e os segmentos não são visíveis aos clientes.</p>
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
