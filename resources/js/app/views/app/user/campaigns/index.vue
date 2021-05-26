<template>
  <data-table
    model="Platform\Models\Campaign"
  >
    <template v-slot:empty-head>
      <v-icon size="72" :color="$store.getters.app.color_name">record_voice_over</v-icon>
    </template>

    <template v-slot:empty-text>
      <p class="subheading">Uma campanha é um site onde os clientes podem registar-se, ganhar pontos e trocá-los por prémios.</p>
        <p class="subheading">Ao criar uma campanha será gerado um endereço web <strong>cartaocliente.com/campaign/nomedacampanha</strong> onde os seus clientes podem aceder à campanha. Se quiser, pode criar a sua campanha num domínio seu, por exemplo <strong>campanha.omeunegocio.pt</strong> ou <strong>omeunegocio.pt</strong></p>
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
