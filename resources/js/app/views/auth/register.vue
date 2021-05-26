<template>
  <v-container fluid fill-height>
    <v-layout align-center justify-center>
      <v-flex xs10 sm7 md5 lg3 xl2>
        <v-form
          v-if="!form1.success"
          data-vv-scope="form1"
          :model="form1"
          lazy-validation
          @submit.prevent="submitForm('form1')"
          autocomplete="off"
          method="post"
          >
          <v-card class="elevation-18">
            <v-toolbar flat color="transparent">
              <v-toolbar-title>{{ $t('register_head') }}</v-toolbar-title>
            </v-toolbar>
            <v-card-text>
            <v-alert
              :value="form1.has_error && !form1.success"
              type="error"
              class="mb-4"
              >
              <span v-if="form1.error == 'registration_validation_error'">{{ $t('server_error') }}</span>
              <span v-else>{{ $t('correct_errors') }}</span>
            </v-alert>

            <v-text-field
              v-model="form1.name"
              data-vv-name="name"
              data-vv-as="Nome"
              v-validate="'required|min:2|max:32'"
              :label="$t('enter_your_name')"
              :error-messages="errors.collect('form1.name')"
              required
              prepend-inner-icon="person"
              ></v-text-field>

            <v-text-field
              type="email"
              v-model="form1.email"
              data-vv-name="email"
              data-vv-as="E-mail"
              v-validate="'required|max:64|email'"
              :label="$t('enter_email')"
              :error-messages="errors.collect('form1.email')"
              required
              prepend-inner-icon="email"
              ></v-text-field>

            <v-text-field
              v-model="form1.password"
              data-vv-name="password"
              data-vv-as="Palavra-passe"
              v-validate="'required|min:8|max:24'"
              :label="$t('enter_password')"
              :error-messages="errors.collect('form1.password')"
              :type="show_password ? 'text' : 'password'"
              :append-icon="show_password ? 'visibility' : 'visibility_off'"
              @click:append="show_password = !show_password"
              required
              prepend-inner-icon="lock"
              ></v-text-field>

              <v-checkbox
                type="checkbox"
                v-model="form1.terms"
                data-vv-name="terms"
                data-vv-as="Termos"
                v-validate="'required'"
                :label="$t('agree_to_terms')"
                :error-messages="errors.collect('form1.terms')"
                value="1"
                required
                >
                <template v-slot:label>
                  <div>
                    {{ $t('i_agree_to') }}
                    <v-tooltip bottom>
                      <template v-slot:activator="{ on }">
                        <a
                          target="_blank"
                          href="/legal"
                          @click.stop
                          v-on="on"
                        >
                          {{ $t('terms_and_policy') }}
                        </a>
                      </template>
                        Abre num separador novo
                    </v-tooltip>
                  </div>
                </template>
              </v-checkbox>

            </v-card-text>

            <v-card-actions>
              <v-btn :color="$store.getters.app.color_name" large block :loading="form1.loading" :disabled="form1.loading" type="submit">{{ $t('create') }}</v-btn>
            </v-card-actions>
            <v-card-actions class="pt-0">
              <v-btn @click="toLogin" :disabled="form1.loading" large block text>{{ $t('back_to_login') }}</v-btn>
            </v-card-actions>
          </v-card>
        </v-form>
      </v-flex>
    </v-layout>
  </v-container>
</template>
<script>
  export default {
    $_veeValidate: {
      validator: 'new'
    },
    data() {
      return {
        show_password: false,
        termsDialogVisible: false,
        disclaimer: '',
        form1: {
          loading: false,
          terms: '',
          name: '',
          email: '',
          password: '',
          locale: '',
          timezone: '',
          has_error: false,
          error: '',
          errors: {},
          success: false
        }
      }
    },
    created () {
      this.form1.locale = Intl.DateTimeFormat().resolvedOptions().locale || null
      this.form1.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || null
    },
    methods: {
      submitForm(formName) {
        this[formName].has_error = false
        this[formName].loading = true

        this.$validator.validateAll(formName).then((valid) => {
          if (valid) {
            this.register(formName);
          } else {
            this[formName].loading = false
            return false;
          }
        });
      },
      toLogin() {
        this.$router.push({name: 'login'})
      },
      register(formName) {
        var app = this[formName]
        this.$auth.register({
          data: {
            language: this.$i18n.locale,
            name: app.name,
            email: app.email,
            password: app.password,
            locale: app.locale,
            timezone: app.timezone,
            terms: app.terms
          },
          success: function () {
            app.success = true
            this.$router.push({name: 'login', params: {successRegistrationRedirect: true, email: app.email}})
          },
          error: function (res) {
            app.has_error = true
            app.error = res.response.data.error
            app.errors = res.response.data.errors || {}

            for (let field in app.errors) {
              this.$validator.errors.add({
                field: formName + '.' + field,
                msg: app.errors[field][0]
              })
            }
            app.loading = false
          }
        })
      }
    },
  }
</script>
<style scoped>
</style>
