<template>
    <v-card>
        <v-card-title class="headline">Creditar pontos</v-card-title>
        <v-form
            data-vv-scope="customerNumber"
            :model="customerNumber"
            @submit.prevent="creditCustomer"
            autocomplete="off"
            method="post"
            accept-charset="UTF-8"
        >
            <v-card-text>
                <p class="body-1" v-if="customerNumber.credited === false">Introduza o número de pontos a creditar.</p>
                <p class="body-1" v-if="customerNumber.credited === true">Pontos adicionados com sucesso.</p>

                <v-list three-line>
                    <v-list-item >
                        <v-row>
                            <v-col xs11 class="pa-0 col-sm-6  col-md-6" style="height: 74px;">
                                <div class="pt-0 pb-0 d-flex align-items-center" style="padding-left: 4px;">

                                    <v-list-item-avatar class="mt-2 d-inline-block">
                                        <v-avatar
                                            class=" list-inline-item"
                                            :size="56"
                                            color="grey"
                                        >
                                            <v-img :src="formdata.avatar"></v-img>
                                        </v-avatar>
                                    </v-list-item-avatar>

                                    <v-list-item-content class="d-inline-block">
                                        <div class="mt-0">
                                            <v-list-item-title v-html="formdata.name"></v-list-item-title>
                                            <v-list-item-subtitle v-html="formdata.number"></v-list-item-subtitle>
                                            <v-list-item-subtitle><v-icon size="17">toll</v-icon> <span v-html="new Intl.NumberFormat(formdata.locale.replace('_', '-')).format(formdata.points)"></span></v-list-item-subtitle>
                                        </div>
                                    </v-list-item-content>
                                </div>
                            </v-col>
                            <v-col align-center justify-center class="pa-0" style="height: 84px">
                                <v-list-item v-html="parseDateTime(formdata.expiration, 'DD-MM-YYYY', '#D32F2F', '#4CAF50')" class="pa-0"></v-list-item>
                            </v-col>
                        </v-row>

                    </v-list-item>
                </v-list>

                <div class="mb-3">
                    <a href="javascript:void(0);" v-if="customerNumber.credited !== false" @click="customerNumber.credited = false">Adicionar mais pontos</a>
                </div>

                <v-text-field
                    :disabled="customerNumber.credited !== false"
                    type="number"
                    v-model="customerNumber.points"
                    outline
                    label="Pontos a serem creditados"
                    prepend-inner-icon="toll"
                    data-vv-name="points"
                    data-vv-as="Pontos"
                    v-validate="'required|numeric|max_value:100000'"
                    :error-messages="errors.collect('customerNumber.points')"
                ></v-text-field>

<!--                <v-text-field-->
<!--                    :disabled="customerNumber.credited !== false"-->
<!--                    type="text"-->
<!--                    v-model="formdata.number"-->
<!--                    outline-->
<!--                    label="Número de cliente"-->
<!--                    data-vv-name="number"-->
<!--                    data-vv-as="Número"-->
<!--                    v-validate="'required|min:11|max:11'"-->
<!--                    :placeholder="formdata.number"-->
<!--                    v-mask="'###-###-###'"-->
<!--                    prepend-inner-icon="person"-->
<!--                    :error-messages="errors.collect('customerNumber.number')"-->
<!--                ></v-text-field>-->

                <v-autocomplete
                    :disabled="customerNumber.credited !== false"
                    v-if="Object.keys(segments).length > 0"
                    v-model="customerNumber.segments"
                    :items="segments"
                    item-value="0"
                    item-text="1"
                    label="Segments (opcional)"
                    hide-no-data
                    hide-selected
                    chips
                    multiple
                    prepend-inner-icon="category"
                    deletable-chips
                ></v-autocomplete>


            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" :loading="customerNumber.loading" :disabled="customerNumber.credited !== false" type="submit">Adicionar pontos</v-btn>
                <v-btn color="secondary" text @click="$emit('data-list-events', {'closeDialog': true, 'reload': true})">Fechar</v-btn>
            </v-card-actions>
        </v-form>
    </v-card>
</template>

<script>
    import {copyElById, unmask} from '../../utils/helpers';
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import moment from "moment";

    export default {
        $_veeValidate: {
            validator: 'new'
        },
        data: () => {
            return {
                segments: [],
                customerNumber: {
                    loading: false,
                    credited: false,
                    points: null,
                    segments: [],
                    number: null
                },
                dialog: {
                    claim: {
                        customerNumber: false
                    }
                },
                form: [],
                formdata: {}
            }
        },
        props: {
            api: {
                default: '/staff/data-form',
                required: false,
                type: String
            },
            model: {
                default: '',
                required: false,
                type: String
            },
            uuid: {
                default: null,
                required: false,
                type: String
            }
        },
        mounted () {
            axios
                .get('/staff/segments', { params: { locale: this.$i18n.locale, campaign: this.$store.state.app.campaign.uuid }})
                .then(response => {
                    this.segments = _.toPairs(response.data)

                })
        },
        created () {
            moment.locale(this.$auth.user().locale)

        },
        beforeMount () {
            this.getDataFromApi()
                .then(data => {
                    this.form = data.form
                    console.log(this.form);
                    this.tabCount = Object.keys(this.form.items).length
                })
        },
        methods: {
            copyElById,
            unmask,
            creditCustomer () {
                this.customerNumber.loading = true
                // validation
                this.$validator.validateAll('customerNumber').then((valid) => {
                    if (! valid) {
                        this.customerNumber.loading = false
                        return false
                    } else {
                        axios
                            .post('/staff/points/customer/credit', {
                                locale: this.$i18n.locale,
                                campaign: this.$store.state.app.campaign.uuid,
                                points: this.customerNumber.points,
                                number: this.unmask(this.formdata.number, '###-###-###'),
                                segments: this.customerNumber.segments

                            })
                            .then(response => {
                                if (response.data.status === 'success') {
                                    this.customerNumber.credited = true
                                }
                                this.customerNumber.credited = true
                                this.customerNumber.loading = false
                                this.$emit('data-list-events', {'closeDialog': false, 'reload': false})
                            })
                            .catch(err => {

                                let errors = err.response.data.errors || {}

                                for (let field in errors) {
                                    this.$validator.errors.add({
                                        field: 'customerNumber.' + field,
                                        msg: errors[field]
                                    })
                                }
                                this.customerNumber.loading = false
                            })
                    }
                });
            },
            getDataFromApi () {
                this.loading = true
                return new Promise((resolve, reject) => {
                    let that = this
                    axios.get(this.api, {
                        params: {
                            locale: this.$i18n.locale,
                            model: this.model,
                            uuid: this.uuid
                        }
                    })
                        .then(res => {
                            if (res.data.status === 'success') {
                                let form = {}

                                form.items = res.data.form
                                form.loading = false
                                form.error = ''
                                form.errors = {}
                                form.has_error = false
                                form.success = false

                                that.settings = res.data.settings
                                that.formdata = res.data.values
                                that.customerNumber.points = res.data.values.pre_points;
                                // that.relations = res.data.relations
                                // that.count = res.data.count
                                // that.max = res.data.max
                                // that.loading = false
                                // that.loadingForm = false

                                // Dates
                                for (let date of res.data.dates) {
                                    if (that.formdata[date] !== null) {
                                        that.formdata[date] =  new Date(that.formModel[date])
                                    }
                                }

                                resolve({
                                    form
                                })
                            }
                        })
                        .catch(err => console.log(err.response.data))
                        .finally(() => that.customerNumber.loading = false)
                })
            },
            parseDateTime (datetime, format, color_is_past, color_is_future) {
                moment.locale(this.$auth.user().locale)

                let color = null
                let icon = null
                if (typeof color_is_past !== 'undefined' && moment(datetime).isBefore(moment())) {
                    color = color_is_past
                    icon = 'cancel'
                }
                if (typeof color_is_future !== 'undefined' && moment(datetime).isAfter(moment())) {
                    color = color_is_future
                    icon = 'check_circle'
                }

                if (datetime === null) {
                    datetime = ''
                } else {
                    datetime = (format == 'ago') ? moment(datetime).fromNow() : moment(datetime).format(format)
                }

                if (color !== null) {
                    // return '<v-list-item-title class="mx-auto d-flex justify-content-center"><i data-v-4446faf9="" aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 56px; color: '+color+'">'+icon+'</i><span class="flex justify-center font-weight-light" style="font-size: 1rem"><div style="line-height: 1.2; text-align: center;flex: 1 1 100%">'+datetime+'</div><div style="line-height: 1.2; font-size: 0.875rem; text-align: center; flex: 1 1 100%">Validade</div></span></div>'

                    // return '<div class="v-avatar v-list-item__avatar mt-0" style="min-width: 64px; width: 64px; margin-right: 8px"><i aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 64px; color: '+color+'">'+icon+'</i></div><div class="v-list-item__content pt-0"><div class="v-list-item__title">'+datetime+'</div><div class="v-list-item__subtitle">Validade</div></div>'

                    return '<div class="v-avatar v-list-item__avatar mt-1" style="min-width: 64px; width: 64px; margin-right: 8px"><i aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 64px; color: '+color+'">'+icon+'</i></div><div class="v-list-item__content pt-0 pl-1"><div class="mt-0"><div class="v-list-item__title">'+datetime+'</div><div class="v-list-item__subtitle">Validade</div></div></div> '
                    // return '<div class="mx-auto d-flex justify-content-center"><i data-v-4446faf9="" aria-hidden="true" class="v-icon notranslate material-icons" style="font-size: 36px; color: '+color+'">'+icon+'</i><span class="flex justify-center font-weight-light" style="font-size: 16px"><div style="height: 18px; text-align: center">'+datetime+'</div><div style="height: 18px; font-size: 16px; text-align: center">Validade</div></span></div>'

                } else {
                    return ''
                }
            },
        },

        computed: {
            campaign () {
                return this.$store.state.app.campaign
            },
            claimOptions () {
                return  [
                    {
                        active: (_.indexOf(this.campaign.claimOptions, 'customerNumber') >= 0) ? true : false,
                        id: 'customerNumber',
                        icon: 'card_giftcard',
                        title: 'Customer Number',
                        description: 'Add points to a customer account using a customer number.'
                    }
                ]
            }
        }
    }
</script>

<style scoped>
</style>
