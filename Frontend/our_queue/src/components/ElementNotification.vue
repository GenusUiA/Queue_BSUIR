<script setup>
const props = defineProps({
  notification: {
    type: Object,
    required: true,
  },
});

async function confirm(){
	const response = await fetch(
    `https://localhost:7243/api/exchange/${props.notification.id}`,
    {
			method: "PATCH",
      credentials: "include",
    }
  );
  // try {
  //   if (response.ok) {
  //     const data = await response.json();
  //     return data;
  //   } else return 0;
  // } catch {
  //   return 0;
  // }
}

async function cancel(){
	const response = await fetch(
    `https://localhost:7243/api/exchange/${props.notification.id}`,
    {
			method: "DELETE",
      credentials: "include",
    }
  );
}

</script>

<template>
  <div class="element">
    <p>
      Пользователь {{ notification.userName }} ({{ notification.number }}-ое
      место) хочет поменяться с вами местами на предмет
      {{ notification.subjectName }} ({{ notification.date }})
			<div class="elementFooter">
				<span>{{ notification.createdAt }}</span>
				<button class="buttonConfirm" @click="confirm">Подтвердить</button>
				<button class="buttonCancel" @click="cancel">Отменить</button>
			</div>
    </p>
  </div>
</template>

<style scoped>
	.element{
		display: flex;
		flex-direction: column;
		
	}

	.elementFooter{
		display: flex;
		gap: 0.5rem;
		align-items: center;
		margin-top: 0.3rem;
	}

	.buttonCancel{
		padding: 0.3rem;
    border: none;
    border-radius: 0.3rem;
	}

	.buttonConfirm{
		padding: 0.3rem;
    border: none;
    border-radius: 0.3rem;
		background-color: #0392ff;
		color: white;
	}
</style>
