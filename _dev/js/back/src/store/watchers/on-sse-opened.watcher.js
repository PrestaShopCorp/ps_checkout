import { SseOnboardingService } from '@/service/sse-onboarding.service';

let sseOnboardingService = null;

export const onSseOpened = state => {
  const { session } = state;
  const { onboarding } = session || {};
  const { is_sse_opened } = onboarding || {};

  return is_sse_opened === '1';
};

export const onSseOpenedWatcher = store => {
  sseOnboardingService = new SseOnboardingService(store);

  if (onSseOpened(store.state)) {
    sseOnboardingService.open();
  }

  return value => {
    if (value) {
      sseOnboardingService.open();
    } else {
      sseOnboardingService.close();
    }
  };
};
