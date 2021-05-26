<template>
  <div style="height: 100%">
    <v-container fluid v-if="!loaded" style="height: 100%">
      <v-layout align-center justify-center row fill-height class="text-xs-center" style="height: 100%">
        <v-progress-circular
          :size="50"
          :color="app.color_name"
          indeterminate
          class="ma-5"
        ></v-progress-circular>
      </v-layout>
    </v-container>

    <v-card v-show="loaded">
      <v-toolbar flat color="transparent">
        <v-toolbar-title>{{ translations.items }}</v-toolbar-title>

        <v-spacer></v-spacer>

        <v-text-field
          v-if="loaded && ((rows.length == 0 && filteredResults) || parseInt(rows.length) > 0)"
          v-model="search"
          append-icon="search"
          label="Pesquisar"
          single-line
          clearable
          flat
          solo-inverted
          hide-details
          :style="{'max-width': ($vuetify.breakpoint.xs) ? '135px' : '320px'}"
        ></v-text-field>

        <v-menu offset-y v-if="parseInt(rows.length) > 0 && showExport">
          <template v-slot:activator="{ on }">
            <v-btn class="ml-1" icon v-on="on">
              <v-icon>more_vert</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="exportRows">
              <v-list-item-icon>
                <v-icon>insert_drive_file</v-icon>
              </v-list-item-icon>
              <v-list-item-title>Download Excel</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>

      </v-toolbar>

      <div v-if="!filteredResults && !loading && loaded && rows.length == 0" class="text-xs-center">
        <div>
          <slot name="empty-head"></slot>
        </div>
        <h1 class="title my-4">Ainda não criou {{ translations.items_lowercase }}</h1>
        <div class="mx-5" :class="{'pb-4': !settings.create}">
          <slot name="empty-text"></slot>
        </div>
        <div>
          <v-btn @click="uuid = null; dataForm = true" v-if="settings.create === true" large :color="app.color_name" class="mt-3 darken-2-4 mb-5"><v-icon class="mr-2">add</v-icon> {{ translations.create_item }}</v-btn>
        </div>
      </div>

      <v-toolbar flat color="transparent" v-show="filters.length > 0 && loaded && ((rows.length == 0 && filteredResults) || parseInt(rows.length) > 0)">
        <v-spacer></v-spacer>
        <div v-for="(filter, filter_index) in filters">

          <v-autocomplete
            @change="changeFilter"
            v-model="filter.model"
            :items="filter.items"
            :placeholder="filter.placeholder"
            :prepend-inner-icon="filter.icon"
            item-text="val"
            item-value="pk"
            flat
            dense
            chips
            multiple
            solo
            clearable
            hide-no-data
            hide-details
            deletable-chips
          ></v-autocomplete>

        </div>
      </v-toolbar>

      <v-btn
        v-if="((rows.length == 0 && filteredResults) || parseInt(rows.length) > 0) && settings.create === true"
        color="pink"
        dark
        large
        fixed
        right
        bottom
        fab
        id="createBtn"
        @click="uuid = null; dataForm = true"
        @_mouseover="showFabToolTip = true"
        @_mouseleave="showFabToolTip = false"
        >
        <v-icon>add</v-icon>
      </v-btn>

      <v-tooltip bottom v-model="showFabToolTip" activator="#createBtn" v-if="((rows.length == 0 && filteredResults) || parseInt(rows.length) > 0) && settings.create === true">
        <span>{{ translations.create_item }}</span>
      </v-tooltip>

      <v-data-table
        :mobile-breakpoint="0"
        class="mb-5"
        v-show="loaded && ((rows.length == 0 && filteredResults) || parseInt(rows.length) > 0)"
        v-model="selected"
        :headers="headers"
        :items="rows"
        :loading="loading"
        item-key="uuid"
        :options.sync="options"
        :sort-desc.sync="descending"
        :server-items-length="totalItems"
        :show-select="settings.select_all"
        footer-props.prev-icon="arrow_left"
        footer-props.next-icon="arrow_right"
        header-props.sort-icon="arrow_drop_down"
        :items-per-page-options="itemsPerPageOptions"
      >

        <template :slot="'item.' + item.value" slot-scope="props" v-for="(item, index) in headers">


          <div v-if = "shelfLife.length != 0">
              <div v-if="index != Object.keys(headers).length - 2 || ! settings.actions" :style="item.style">
                  <div v-if="item.type === 'boolean'" class="text-xs-center">
                      <div v-if="props.item[item.value] === 1 || props.item[item.value] === '1'">
                          <v-icon small>check</v-icon>
                      </div>
                      <div v-else>
                          <v-icon small>fechar</v-icon>
                      </div>
                  </div>
                  <div v-else-if="item.type === 'link'">
                      <a :href="props.item[item.value]" target="_blank">{{ props.item[item.value].replace('http://', '').replace('https://', '').replace('//', '') }}</a>
                  </div>
                  <div v-else-if="item.type === 'campaign_link'">
                      <a :href="props.item[item.value] + '/staff'" target="_blank" style="font-weight: bold">Área de Funcionário</a> | <a :href="props.item[item.value]" target="_blank">{{ props.item[item.value].replace('http://', '').replace('https://', '').replace('//', '') }}</a>
                  </div>
                  <div v-else-if="item.type === 'number' || item.type === 'currency'">
                      <div class="text-xs-right">{{ props.item[item.value] }}</div>
                  </div>
                  <div v-else-if="item.type === 'date_time'">
                      <span v-html="parseDateTime(props.item[item.value], item.format, item.color_is_past, item.color_is_future)"></span>
                  </div>
                  <div v-else-if="item.type === 'date'" style = "padding-top:8px">
                      <div style="border-radius:5px; background-color: rgba(0, 0, 0, 0.16)" class="v-input__slot">
                          <span v-html="parseDateTime1(props.item[item.value], 'YYYY-MM-DD', '#D32F2F', '#4CAF50')"></span>

                          <span v-for="(item, index) in shelfLife">
                      <v-tooltip top >
                        <template v-slot:activator="{ on }">
                          <v-btn class="mr-1 mt-0 mb-0" v-on="on" :dark="item.dark" :color="item.color" icon small @click="editExpire(item.action, props.item.uuid, props.item[item.value])"><v-icon small>{{ item.icon }}</v-icon></v-btn>
                        </template>
                        <span>{{ item.text }}</span>
                      </v-tooltip>
                    </span>
                      </div>
                  </div>
                  <div v-else-if="item.type === 'image'">
                      <v-img :src="props.item[item.value]" class="elevation-1 my-3 d-block" v-if="props.item[item.value]" contain :max-width="item.max_width" :max-height="item.max_height"></v-img>
                  </div>
                  <div v-else-if="item.value === 'avatar'" class="text-xs-center">
                      <v-avatar
                          :tile="false"
                          size="32"
                          color="grey lighten-4"
                      >
                          <v-img :src="props.item[item.value]" v-if="props.item[item.value]"></v-img>
                      </v-avatar>
                  </div>
                  <div v-else>
                      {{ props.item[item.value] }}
                  </div>
              </div>

              <div v-if="index == Object.keys(headers).length - 2 && settings.actions" :style="{width: settings.actions_width}">

                <span v-for="(item, index) in actions">
                  <v-tooltip top v-if="item.action">
                    <template v-slot:activator="{ on }">
                      <v-btn class="mr-1 mt-0 mb-0" v-on="on" :dark="item.dark" :color="item.color" icon small @click="executeAction(item.action, props.item.uuid)"><v-icon small>{{ item.icon }}</v-icon></v-btn>
                    </template>
                    <span>{{ item.text }}</span>
                  </v-tooltip>
                  <v-divider v-else vertical class="grey lighten-2" style="vertical-align: middle;"></v-divider>
                </span>
              </div>
          </div>
          <div v-else>
              <div v-if="index != Object.keys(headers).length - 1 || ! settings.actions" :style="item.style">
                  <div v-if="item.type === 'boolean'" class="text-xs-center">
                      <div v-if="props.item[item.value] === 1 || props.item[item.value] === '1'">
                          <v-icon small>check</v-icon>
                      </div>
                      <div v-else>
                          <v-icon small>fechar</v-icon>
                      </div>
                  </div>
                  <div v-else-if="item.type === 'link'">
                      <a :href="props.item[item.value]" target="_blank">{{ props.item[item.value].replace('http://', '').replace('https://', '').replace('//', '') }}</a>
                  </div>
                  <div v-else-if="item.type === 'campaign_link'">
                      <a :href="props.item[item.value] + '/staff'" target="_blank" style="font-weight: bold">Área de Funcionário</a> | <a :href="props.item[item.value]" target="_blank">{{ props.item[item.value].replace('http://', '').replace('https://', '').replace('//', '') }}</a>
                  </div>
                  <div v-else-if="item.type === 'number' || item.type === 'currency'">
                      <div class="text-xs-right">{{ props.item[item.value] }}</div>
                  </div>
                  <div v-else-if="item.type === 'date_time'">
                      <span v-html="parseDateTime(props.item[item.value], item.format, item.color_is_past, item.color_is_future)"></span>
                  </div>
                  <div v-else-if="item.type === 'image'">
                      <v-img :src="props.item[item.value]" class="elevation-1 my-3 d-block" v-if="props.item[item.value]" contain :max-width="item.max_width" :max-height="item.max_height"></v-img>
                  </div>
                  <div v-else-if="item.value === 'avatar'" class="text-xs-center">
                      <v-avatar
                          :tile="false"
                          size="32"
                          color="grey lighten-4"
                      >
                          <v-img :src="props.item[item.value]" v-if="props.item[item.value]"></v-img>
                      </v-avatar>
                  </div>
                  <div v-else>
                      {{ props.item[item.value] }}
                  </div>
              </div>
              <div v-if="index == Object.keys(headers).length - 1 && settings.actions" :style="{width: settings.actions_width}">
                <span v-for="(item, index) in actions">
                  <v-tooltip top v-if="item.action">
                    <template v-slot:activator="{ on }">
                      <v-btn class="mr-1 mt-0 mb-0" v-on="on" :dark="item.dark" :color="item.color" icon small @click="executeAction(item.action, props.item.uuid)"><v-icon small>{{ item.icon }}</v-icon></v-btn>
                    </template>
                    <span>{{ item.text }}</span>
                  </v-tooltip>
                  <v-divider v-else vertical class="grey lighten-2" style="vertical-align: middle;"></v-divider>
                </span>
              </div>
          </div>

        </template>

        <template v-slot:footer>
          <div class="ma-3">
            <v-btn v-if="settings.select_all" depressed small color="error" :disabled="disableDeleteSelected" @click="deleteSelected">{{ $t('delete_selected') }}</v-btn>
          </div>
        </template>

        <template v-slot:no-data>
          <div v-if="! loading" class="text-xs-center">
              A sua pesquisa e/ou filtro não tem resultados.
          </div>
          <div v-if="loading" class="text-xs-center">
            Loading data
          </div>
        </template>

      </v-data-table>

      <v-dialog
          :fullscreen="$vuetify.breakpoint.xsOnly"
          v-model="display"
          @keydown.esc="display = false"
          eager
          persistent
          :width="width"
          :disabled="disabled"
      >

          <v-card>
              <v-card-text style="padding: 0;">
                  <v-tabs fixed-tabs v-model="activeTab">
                      <v-tab key="calendar">
                          <slot name="dateIcon">
                              <v-icon>event</v-icon>
                          </slot>
                      </v-tab>
                      <v-tab key="timer" :__disabled="!dateSelected">
                          <slot name="timeIcon">
                              <v-icon>access_time</v-icon>
                          </slot>
                      </v-tab>
                      <v-tab-item key="calendar">
                          <v-date-picker
                              full-width
                              v-model="datePart"
                              scrollable
                              locale="pt-BR"
                              actions

                          >
                          </v-date-picker>
                      </v-tab-item>
                      <v-tab-item key="timer">
                          <v-time-picker
                              ref="timer"
                              class="v-time-picker-custom"
                              v-model="timePart"
                              scrollable
                              :format="timePickerFormat"
                              actions
                          >
                          </v-time-picker>
                      </v-tab-item>
                  </v-tabs>
              </v-card-text>
              <v-card-actions>
                  <v-spacer></v-spacer>
                  <slot name="actions"
                        :parent="this"
                  >
                      <v-btn color="blue darken-2" text @click="okHandler">{{okText}}</v-btn>
                      <v-btn color="red lighten-1" text @click.native="clearHandler">{{clearText}}</v-btn>
                      <v-btn color="secondary" text @click.native="display = false">{{$t('close')}}</v-btn>
                  </slot>
              </v-card-actions>
          </v-card>
          <v-overlay :value="dateLoading" v-if="!loadingCalendar">
              <v-progress-circular indeterminate size="64"></v-progress-circular>
          </v-overlay>

      </v-dialog>
      <v-dialog
        persistent
        :width="settings.dialog_width || 480"
        :fullscreen="$vuetify.breakpoint.xsOnly"
        v-model="dataForm"
        :dataForm="dataForm"
        @keydown.esc="dataForm = false"
      >
        <data-form
          v-if="dataForm"
          v-on:data-list-events="dataListEvents = $event"
          :model="model"
          :uuid="uuid"
        ></data-form>
      </v-dialog>

    </v-card>
  </div>
</template>
<script>
  import Cookies from 'js-cookie'
  import _ from 'lodash'
  import moment from "moment";

  const DEFAULT_DATE_FORMAT = 'YYYY-MM-DD'
  const DEFAULT_TIME_FORMAT = 'HH:mm'
  const DEFAULT_TIME = '00:00'

  export default {
    name: 'date-time-picker',
    model: {
        prop: 'datetime',
        event: 'input'
    },
    data: () => {
      return {
        descending: false,
        search: '',
        showExport: false,
        totalItems: 0,
        pageCount: 0,
        dataListEvents: null,
        filteredResults: false,
        showFabToolTip: false,
        dataForm: false,
        uuid: null,
        disableDeleteSelected: true,
        loading: true,
        loaded: false,
        selected: [],
        settings: [],
        headers: [],
        actions: [],
        translations: [],
        rows: [],
        filters: [],
        itemsPerPageOptions: [10, 25, 50, 75, 100],
        watchPagination: false,
        options: {
          page: 1,
          itemsPerPage: 10,
          filters: []
        },
        optionsOld: [],
        shelfLife:[],
        display: false,
        activeTab: 0,
        picker: new Date().toISOString().substr(0, 10),
        dateSelected: false,
        timeSelected: false,
        selectedDatetime: null,
        loadingCalendar: true,
        dateLoading: null
      }
    },
    props: {
      api: {
        default: '/app/data-table',
        required: false,
        type: String
      },
      create: {
        default: null,
        required: false,
        type: String
      },
      edit: {
        default: null,
        required: false,
        type: String
      },
      model: {
          default: '',
          required: false,
          type: String,
      },
      datetime: {
          type: [Date, String],
          default: ""
      },
      timePickerFormat: {
          type: String,
          default: 'ampm'
      },
      format: {
          type: String,
          default: 'YYYY-MM-DD HH:mm:ss'
      },
      width: {
          type: Number,
          default: 320
      },
      disabled: {
          type: Boolean,
          default: false
      },
      locale: {
          type: String,
          default: 'en-us'
      },
      clearText: {
          type: String,
          default: 'Limpar'
      },
      okText: {
          type: String,
          default: 'OK'
      },
    },
    watch: {
      options: {
        handler (val, old) {
          if (this.watchPagination) {
            let string_val = String(JSON.stringify(val))
            let string_old = String(this.optionsOld)

            if (string_val !== string_old) {
              this.reloadData()
            }

            this.optionsOld = string_old
          }
        },
        deep: true
      },
      search () {
        this.searchData()
      },
      selected () {
        this.disableDeleteSelected = (this.selected.length > 0) ? false : true;
      },
      dataListEvents () {
        if (this.dataListEvents.closeDialog) {
          this.dataForm = false
        }
        if (this.dataListEvents.reload) {
          this.reloadData()
        }
      }
    },
    mounted () {
      if (typeof this.$route.params.showSnackbar !== 'undefined') {
        this.$root.$snackbar(this.$t(this.$route.params.showSnackbar))
      }
      this.getDataFromApi()
        .then(data => {
          this.rows = data.items
          this.watchPagination = true
        })
    },
    methods: {
      searchData: _.debounce(function(string) {
        this.loading = true
        this.reloadData()
      }, 400),
      getDataFromApi () {
        this.loading = true
        return new Promise((resolve, reject) => {
          let that = this

          axios.get(this.api, {
            params: {
              locale: this.$i18n.locale,
              model: this.model,
              search: this.search,
              page: this.options.page,
              itemsPerPage: this.options.itemsPerPage,
              sortBy: this.options.sortBy,
              descending: this.descending,
              filters: this.options.filters
            }
            })
          .then(res => {
            if (res.data.status === 'success') {
              that.headers = Object.keys(res.data.headers).map((k) => res.data.headers[k])
              that.filters = res.data.filters
              that.settings = res.data.settings
              that.filteredResults = res.data.filteredResults
              that.actions = res.data.actions
              that.showExport = res.data.showExport
              that.translations = res.data.translations
              that.totalItems = res.data.total
              that.loading = false
              that.loaded = true
              that.loadingCalendar = false
              that.dateLoading = false
              if(res.data.shelfLife){
                  that.shelfLife = res.data.shelfLife
                  console.log(that.shelfLife)
                  console.log('===shelfLife===')
                  console.log(that.shelfLife.length)
              }

              console.log(that.headers)

              console.log(res.data.settings)

              let items = res.data.records
              console.log(items)
              resolve({
                items
              })
            }
          })
          .catch(err => console.log(err.response.data))
          .finally(() => that.loading = false)
        })
      },
      reloadData () {
        this.watchPagination = false
        this.loading = true
        this.getDataFromApi()
          .then(data => {
            this.rows = data.items
            this.watchPagination = true
          })
      },
      changeFilter () {
        let filters = {};
        for (let f in this.filters) {
          let filter = this.filters[f].column
          let pk = this.filters[f].model
          if (pk.length > 0) filters[filter] = pk
        }
        this.options.filters = filters
      },
      executeAction (action, uuid) {
        if (action == 'delete') {
          this.uuid = null
          this.deleteRecords([uuid])
        }
        if (action == 'edit') {
          this.uuid = uuid
          this.dataForm = true
          //this.$router.push({name: this.edit, params: { uuid: uuid }})
        }
      },
      editExpire(action, uuid, datetime){
          if(datetime){
              if(action == 'date'){
                  this.uuid = uuid
                  this.selectedDatetime = datetime

                  console.log(this.uuid)
                  console.log(datetime)
                  // this.selectedDatetime = moment('2020-03-10', DEFAULT_DATE_FORMAT)
                  this.display = true

              }
          }else{
              if(action == 'date'){
                  this.uuid = uuid
                  console.log(this.uuid)
                  this.getNow()
                  console.log( this.selectedDatetime)
                  this.display = true
              }
          }

      },
      deleteSelected () {
        if (this.selected.length > 0) {
          let uuids = this.selected
          uuids = _.map(uuids, function(o) { return o.uuid; });
          this.deleteRecords(uuids)
        }
      },
      deleteRecords (uuids) {
        this.$root.$confirm(this.$t('delete'), this.$t('confirm_delete'))
        .then((confirm) => {
          if (confirm) {
            this.loading = true

            let that = this
            axios.post(this.api + '/delete', {
                locale: this.$i18n.locale,
                model: this.model,
                uuids: uuids
              })
            .then(res => {
              if (res.data.status === 'success') {
                this.reloadData()
                this.$root.$snackbar(this.$t('items_deleted', { items: res.data.deleted }))
              }
            })
            .catch(err => {
              if (err.response.data.status === 'error') {
                this.$root.$snackbar(err.response.data.msg)
                this.reloadData()
              }
            })
            .finally(() => that.loading = false)
          }
        })
      },
      parseDateTime (datetime, format, color_is_past, color_is_future) {
        moment.locale(this.$auth.user().locale)

        let color = null
        if (typeof color_is_past !== 'undefined' && moment(datetime).isBefore(moment())) {
          color = color_is_past
        }
        if (typeof color_is_future !== 'undefined' && moment(datetime).isAfter(moment())) {
          color = color_is_future
        }

        if (datetime === null) {
          datetime = '-'
        } else {
          datetime = (format == 'ago') ? moment(datetime).fromNow() : moment(datetime).format(format)
        }

        if (color !== null) {
          return '<div style="font-weight:bold;color: ' + color + '">' + datetime + '</div>'
        } else {
          return datetime
        }
      },

      parseDateTime1 (datetime, format, color_is_past, color_is_future) {
          moment.locale(this.$auth.user().locale)

          let color = null
          let icon = null
          if (typeof color_is_past !== 'undefined' && moment(datetime).isBefore(moment())) {
              color = color_is_past
              icon = 'close'
          }
          if (typeof color_is_future !== 'undefined' && moment(datetime).isAfter(moment())) {
              color = color_is_future
              icon = 'check'
          }

          if (datetime === null) {
              datetime = ''
          } else {
              datetime = (format == 'ago') ? moment(datetime).fromNow() : moment(datetime).format(format)
          }

          if (color !== null) {
              return '<div style="width: 90px;color: ' + color + '"><span class="v-btn__content"><i data-v-4446faf9="" aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 16px;">'+icon+'</i>' + datetime + '</span></div>'
          } else {
              return datetime
          }
      },

      getNow: function() {
            const today = new Date();
            const month = (today.getMonth()+1)<10 ? '0'+(today.getMonth()+1):(today.getMonth()+1);
            const day = today.getDate()<10 ? '0'+today.getDate():today.getDate();
            const hour = today.getHours()<10 ? '0'+today.getHours():today.getHours();
            const minute = today.getMinutes()<10 ? '0'+today.getMinutes():today.getMinutes();
            const seconds = today.getSeconds()<10 ? '0'+today.getSeconds():today.getSeconds();
            const date = today.getFullYear()+'-'+month+'-'+day;
            const time = hour + ":" + minute + ":" + seconds;
            const currentDate = date +' '+ time;
            this.selectedDatetime = currentDate;
        },

      okHandler () {

            this.activeTab = 0
            this.dateLoading = true
            this.loading = true

            console.log(this.timeSelected);

            if(this.dateSelected == true){
                this.$refs.timer.selectingHour = true

                let d = new Date();
                d = this.selectedDatetime
                const month = (d.getMonth()+1)<10 ? '0'+(d.getMonth()+1):(d.getMonth()+1);
                const day = d.getDate()<10 ? '0'+d.getDate():d.getDate();
                const hour = d.getHours()<10 ? '0'+d.getHours():d.getHours();
                const minute = d.getMinutes()<10 ? '0'+d.getMinutes():d.getMinutes();
                const seconds = d.getSeconds()<10 ? '0'+d.getSeconds():d.getSeconds();
                const date = d.getFullYear()+'-'+month+'-'+day;
                const time = hour + ":" + minute + ":" + seconds;
                const currentDate = date +' '+ time;
                console.log(this.dateSelected)
                console.log(this.uuid)

                console.log(this.model)
                this.dateSelected = false

                let data = {
                    'model' : this.model,
                    'uuid' : this.uuid,
                    'expiration':currentDate
                }
                this.loading = true
                axios.post(this.api + '/expiration/save', data)
                    .then(res => {
                        if (res.data.status === 'success') {
                            this.display = false
                            this.reloadData()
                            let action = (this.uuid === null) ? 'Item criado com sucesso' : 'Guardado'
                            this.$root.$snackbar(this.$t(action))
                        }
                    })
                    .catch(err => {
                        if (err.response.data.status === 'error') {
                            this.$root.$snackbar(err.response.data.msg)
                            this.reloadData()
                        }
                    })
                    .finally(() => {
                        this.loading = false
                    })
            }
            else{
                this.dateLoading = false
                this.loading = false
                this.display = false
            }


        },

      clearHandler () {
            this.display = false
            this.activeTab = 0

            let currentDate = this.selectedDatetime

            let data = {
                'model' : this.model,
                'uuid' : this.uuid,
                'expiration' : currentDate
            }
            axios.post(this.api + '/expiration/delete', data)
                .then(res => {
                    if (res.data.status === 'success') {
                        // this.display = false
                        this.reloadData()
                        this.$root.$snackbar('Validade eliminados')
                    }
                })
                .catch(err => {
                    if (err.response.data.status === 'error') {
                        this.$root.$snackbar(err.response.data.msg)
                        this.reloadData()
                    }
                })
                .finally(() => {
                    this.loading = false
                })
        },
      exportRows () {
        this.loading = true
        let that = this
        let uuids = this.selected
        uuids = _.map(uuids, function(o) { return o.uuid; })

        axios({
          url: this.api + '/export',
          method: 'GET',
          responseType: 'blob',
          params: {
            locale: this.$i18n.locale,
            model: this.model,
            uuids: uuids
          }
        })
        .then(res => {
          const url = window.URL.createObjectURL(new Blob([res.data]))
          const link = document.createElement('a')
          link.href = url
          link.setAttribute('download', that.translations.items + '.xlsx')
          document.body.appendChild(link)
          link.click()
        })
        .catch(err => {
          if (err.response.data.status === 'error') {
            this.$root.$snackbar(err.response.data.msg)
          }
        })
        .finally(() => this.loading = false)
      }
    },


    computed: {
      app () {
          return this.$store.getters.app
      },
      campaign () {
          return this.$store.state.app.campaign
      },
      datePart: {
          get () {

              let val = this.selectedDatetime ? moment(this.selectedDatetime).format(DEFAULT_DATE_FORMAT) : ''

              return val
          },
          set (val) {

              this.dateSelected = true
              this.activeTab = 1
              console.log(val)
              let date = moment(val, DEFAULT_DATE_FORMAT)
              let hour = this.selectedDatetime ? moment(this.selectedDatetime).hour() : 0
              let minute = this.selectedDatetime ? moment(this.selectedDatetime).minute() : 0
              let input = moment().year(date.year()).month(date.month()).date(date.date()).hour(hour).minute(minute).second(0)
              this.selectedDatetime = input.toDate()

          }
      },

      timePart: {
          get () {
              let val = this.selectedDatetime ? moment(this.selectedDatetime).format(DEFAULT_TIME_FORMAT) : DEFAULT_TIME
              return val
          },
          set (val) {

              this.timeSelected = true
              console.log(val)
              let time = moment(val, DEFAULT_TIME_FORMAT)
              let input = moment(this.selectedDatetime).hour(time.hour()).minute(time.minute()).second(0)
              this.selectedDatetime = input.toDate()
          }
      },
      formattedDatetime () {
          return this.datetime ? moment(this.datetime).format(this.format) : ''
      }
    }
  }
</script>
<style scoped>
</style>
