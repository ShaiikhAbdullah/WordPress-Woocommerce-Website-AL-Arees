import { PayPalCheckbox, handleCheckboxState } from '../Fields';
import data from '../../../utils/data';

const TodoSettingsBlock = ( {
	todos,
	setTodos,
	todosData,
	setTodosData,
	className = '',
} ) => {
	if ( todosData.length === 0 ) {
		return null;
	}

	return (
		<div
			className={ `ppcp-r-settings-block__todo ppcp-r-todo-items ${ className }` }
		>
			{ todosData.map( ( todo ) => (
				<TodoItem
					name="todo_items"
					key={ todo.value }
					value={ todo.value }
					currentValue={ todos }
					changeCallback={ setTodos }
					description={ todo.description }
					changeTodos={ setTodosData }
					todosData={ todosData }
				/>
			) ) }
		</div>
	);
};

const TodoItem = ( props ) => {
	return (
		<div className="ppcp-r-todo-item">
			<div className="ppcp-r-todo-item__inner">
				<PayPalCheckbox
					{ ...{
						...props,
						handleCheckboxState,
					} }
				/>
				<div className="ppcp-r-todo-item__description">
					{ props.description }
				</div>
			</div>
			<div
				className="ppcp-r-todo-item__close"
				onClick={ () =>
					removeTodo(
						props.value,
						props.todosData,
						props.changeTodos
					)
				}
			>
				{ data().getImage( 'icon-close.svg' ) }
			</div>
		</div>
	);
};

const removeTodo = ( todoValue, todosData, changeTodos ) => {
	changeTodos( todosData.filter( ( todo ) => todo.value !== todoValue ) );
};

export default TodoSettingsBlock;
