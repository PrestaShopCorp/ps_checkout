import de from '@Common/assets/json/translations/de/ui.json';
import en from '@Common/assets/json/translations/en/ui.json';
import es from '@Common/assets/json/translations/es/ui.json';
import fr from '@Common/assets/json/translations/fr/ui.json';
import it from '@Common/assets/json/translations/it/ui.json';
import nl from '@Common/assets/json/translations/nl/ui.json';
import pl from '@Common/assets/json/translations/pl/ui.json';
import pt from '@Common/assets/json/translations/pt/ui.json';

export const messages = {
  de,
  en,
  es,
  fr,
  it,
  nl,
  pl,
  pt
};

export const locales = Object.keys(messages);

export default {
  locales,
  messages
};
