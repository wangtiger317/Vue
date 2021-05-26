<template>
    <v-card>
        <v-card-title class="headline">Credit a customer with number</v-card-title>
        <v-form
            data-vv-scope="customerNumber"
            :model="customerNumber"
            @submit.prevent="creditCustomer"
            autocomplete="off"
            method="post"
            accept-charset="UTF-8"
        >
            <v-card-text>
                <p class="body-1" v-if="customerNumber.credited === false">Enter the amount of points and a customer number to credit the customer.</p>
                <p class="body-1" v-if="customerNumber.credited !== false">Customer successfully credited.</p>

                <div class="mb-3">
                    <a href="javascript:void(0);" v-if="customerNumber.credited !== false" @click="customerNumber.credited = false">Add another credit</a>
                </div>

                <v-text-field
                    :disabled="customerNumber.credited !== false"
                    type="number"
                    v-model="customerNumber.points"
                    outline
                    label="Pontos a serem creditados"
                    prepend-inner-icon="toll"
                    data-vv-name="points"
                    v-validate="'required|numeric|max_value:100000'"
                    :error-messages="errors.collect('customerNumber.points')"
                ></v-text-field>

                <v-text-field
                    :disabled="customerNumber.credited !== false"
                    type="text"
                    v-model="formdata.number"
                    outline
                    label="Customer number"
                    data-vv-name="number"
                    v-validate="'required|min:11|max:11'"
                    :placeholder="formdata.number"
                    v-mask="'###-###-###'"
                    prepend-inner-icon="person"
                    :error-messages="errors.collect('customerNumber.number')"
                ></v-text-field>

                <v-autocomplete
                    :disabled="customerNumber.credited !== false"
                    v-if="Object.keys(segments).length > 0"
                    v-model="customerNumber.segments"
                    :items="segments"
                    item-value="0"
                    item-text="1"
                    label="Segments (optional)"
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
                <v-btn color="primary" :loading="customerNumber.loading" :disabled="customerNumber.credited !== false" type="submit">Credit customer</v-btn>
                <v-btn color="secondary" text @click="$emit('data-list-events', {'closeDialog': true, 'reload': false})">Close</v-btn>
            </v-card-actions>
        </v-form>
    </v-card>
</template>

<script>
    import {copyElById, unmask} from '../../utils/helpers';
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

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
                dialog1: {
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
                default: '/app/data-form',
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
                .get('/app/segments', { params: { locale: this.$i18n.locale, campaign: this.$store.state.app.campaign.uuid }})
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
                            .post('/app/points/customer/credit', {
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
                                this.customerNumber.loading = false
                                this.$emit('data-list-events', {'closeDialog': true, 'reload': true})
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
