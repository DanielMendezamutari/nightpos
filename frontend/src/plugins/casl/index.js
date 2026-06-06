import { abilitiesPlugin } from '@casl/vue'
import { ability } from './ability'

export default function (app) {
  const userAbilityRules = useCookie('userAbilityRules')
  const userData = useCookie('userData')

  // userData.permissions es la fuente de verdad (evita reglas CASL obsoletas en cookie)
  let rules = []

  if (userData.value?.permissions?.length) {
    rules = userData.value.permissions.map(slug => ({
      action: 'access',
      subject: slug,
    }))
    userAbilityRules.value = rules
  }
  else if (userAbilityRules.value?.length) {
    rules = userAbilityRules.value
  }

  ability.update(rules)

  app.use(abilitiesPlugin, ability, {
    useGlobalProperties: true,
  })
}
