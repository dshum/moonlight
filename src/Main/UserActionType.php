<?php 

namespace Moonlight\Main;

class UserActionType 
{
	const ACTION_TYPE_ADD_ELEMENT_ID = 1;
	const ACTION_TYPE_SAVE_ELEMENT_ID = 11;
	const ACTION_TYPE_SAVE_ELEMENT_LIST_ID = 12;
	const ACTION_TYPE_COPY_ELEMENT_ID = 21;
	const ACTION_TYPE_COPY_ELEMENT_LIST_ID = 22;
	const ACTION_TYPE_DROP_ELEMENT_TO_TRASH_ID = 31;
	const ACTION_TYPE_DROP_ELEMENT_LIST_TO_TRASH_ID = 32;
	const ACTION_TYPE_DROP_ELEMENT_ID = 41;
	const ACTION_TYPE_DROP_ELEMENT_LIST_ID = 42;
	const ACTION_TYPE_RESTORE_ELEMENT_ID = 51;
	const ACTION_TYPE_RESTORE_ELEMENT_LIST_ID = 52;
	const ACTION_TYPE_MOVE_ELEMENT_ID = 61;
	const ACTION_TYPE_MOVE_ELEMENT_LIST_ID = 62;
	const ACTION_TYPE_BIND_ELEMENT_LIST_ID = 63;
	const ACTION_TYPE_UNBIND_ELEMENT_LIST_ID = 64;
	const ACTION_TYPE_ORDER_ELEMENT_LIST_ID = 71;
	const ACTION_TYPE_PLUGIN_ID = 101;
	const ACTION_TYPE_ACTION_ID = 102;
	const ACTION_TYPE_SEARCH_ID = 201;
	const ACTION_TYPE_ADD_GROUP_ID = 301;
	const ACTION_TYPE_SAVE_GROUP_ID = 311;
	const ACTION_TYPE_DROP_GROUP_ID = 321;
	const ACTION_TYPE_SAVE_ITEM_PERMISSIONS_ID = 331;
	const ACTION_TYPE_SAVE_ELEMENT_PERMISSIONS_ID = 332;
	const ACTION_TYPE_ADD_USER_ID = 401;
	const ACTION_TYPE_SAVE_USER_ID = 411;
	const ACTION_TYPE_DROP_USER_ID = 421;
	const ACTION_TYPE_SAVE_PROFILE_ID = 501;
	const ACTION_TYPE_CHANGE_PASSWORD_ID = 502;
	const ACTION_TYPE_RESET_PASSWORD_ID = 503;
	const ACTION_TYPE_LOGIN_ID = 601;
    const ACTION_TYPE_LOGOUT_ID = 602;

	public static $actionTypeNameList = [
		self::ACTION_TYPE_ADD_ELEMENT_ID => 'Добавление элемента',
		self::ACTION_TYPE_SAVE_ELEMENT_ID => 'Сохранение элемента',
		self::ACTION_TYPE_SAVE_ELEMENT_LIST_ID => 'Сохранение списка элементов',
		self::ACTION_TYPE_COPY_ELEMENT_ID => 'Копирование элемента',
		self::ACTION_TYPE_COPY_ELEMENT_LIST_ID => 'Копирование списка элементов',
		self::ACTION_TYPE_DROP_ELEMENT_TO_TRASH_ID => 'Удаление элемента в корзину',
		self::ACTION_TYPE_DROP_ELEMENT_LIST_TO_TRASH_ID => 'Удаление списка элементов в корзину',
		self::ACTION_TYPE_DROP_ELEMENT_ID => 'Удаление элемента',
		self::ACTION_TYPE_DROP_ELEMENT_LIST_ID => 'Удаление списка элементов',
		self::ACTION_TYPE_RESTORE_ELEMENT_ID => 'Восстановление элемента из корзины',
		self::ACTION_TYPE_RESTORE_ELEMENT_LIST_ID => 'Восстановление списка элементов из корзины',
		self::ACTION_TYPE_MOVE_ELEMENT_ID => 'Перенос элемента',
		self::ACTION_TYPE_MOVE_ELEMENT_LIST_ID => 'Перенос списка элементов',
		self::ACTION_TYPE_BIND_ELEMENT_LIST_ID => 'Привязывание списка элементов',
		self::ACTION_TYPE_UNBIND_ELEMENT_LIST_ID => 'Отвязывание списка элементов',
		self::ACTION_TYPE_ORDER_ELEMENT_LIST_ID => 'Сортировка списка элементов',
		self::ACTION_TYPE_PLUGIN_ID => 'Плагин',
		self::ACTION_TYPE_ACTION_ID => 'Действие',
		self::ACTION_TYPE_SEARCH_ID => 'Поиск элементов',
		self::ACTION_TYPE_ADD_GROUP_ID => 'Добавление группы',
		self::ACTION_TYPE_SAVE_GROUP_ID => 'Сохранение группы',
		self::ACTION_TYPE_DROP_GROUP_ID => 'Удаление группы',
		self::ACTION_TYPE_SAVE_ITEM_PERMISSIONS_ID => 'Сохранение прав доступа по умолчанию',
		self::ACTION_TYPE_SAVE_ELEMENT_PERMISSIONS_ID => 'Сохранение прав доступа к элементам',
		self::ACTION_TYPE_ADD_USER_ID => 'Добавление пользователя',
		self::ACTION_TYPE_SAVE_USER_ID => 'Сохранение пользователя',
		self::ACTION_TYPE_DROP_USER_ID => 'Удаление пользователя',
		self::ACTION_TYPE_SAVE_PROFILE_ID => 'Сохранение профиля',
		self::ACTION_TYPE_CHANGE_PASSWORD_ID => 'Изменение пароля',
		self::ACTION_TYPE_RESET_PASSWORD_ID => 'Сброс пароля',
		self::ACTION_TYPE_LOGIN_ID => 'Авторизация',
        self::ACTION_TYPE_LOGOUT_ID => 'Выход',
	];

	public static function getActionTypeNameList()
	{
		return static::$actionTypeNameList;
	}

	public static function getActionTypeName($actionTypeId)
	{
		return
			isset(static::$actionTypeNameList[$actionTypeId])
			? static::$actionTypeNameList[$actionTypeId]
			: null;
	}

	public static function actionTypeExists($actionTypeId)
	{
		return isset(static::$actionTypeNameList[$actionTypeId]);
	}
}
