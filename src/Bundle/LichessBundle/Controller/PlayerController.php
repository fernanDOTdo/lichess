<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Config\AnybodyGameConfig;
use Bundle\LichessBundle\Config\FriendGameConfig;
use Bundle\LichessBundle\Chess\DrawerConcurrentOfferException;
use Bundle\LichessBundle\Chess\FinisherException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use RuntimeException;

class PlayerController extends Controller
{
    public function outoftimeAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        $this->get('lichess.finisher')->outoftime($player);
        $this->flush();

        return new Response('ok');
    }

    public function rematchAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        $this->get('lichess.rematcher')->rematch($player);
        $this->flush();

        return new Response('ok');
    }

    public function forceResignAction($id)
    {
        $this->get('lichess.finisher')->forceResign($this->get('lichess.provider')->findPlayer($id));
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function offerDrawAction($id)
    {
        try {
            $this->get('lichess.drawer')->offer($this->get('lichess.provider')->findPlayer($id));
        } catch (DrawerConcurrentOfferException $e) {
            return $this->acceptDrawOffer($id);
        }
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function declineDrawOfferAction($id)
    {
        $this->get('lichess.drawer')->decline($this->get('lichess.provider')->findPlayer($id));
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function acceptDrawOfferAction($id)
    {
        $this->get('lichess.drawer')->accept($this->get('lichess.provider')->findPlayer($id));
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function cancelDrawOfferAction($id)
    {
        $this->get('lichess.drawer')->cancel($this->get('lichess.provider')->findPlayer($id));
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function claimDrawAction($id)
    {
        $this->get('lichess.finisher')->claimDraw($this->get('lichess.provider')->findPlayer($id));
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function moveAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        $this->get('lichess.mover')->move($player, $this->get('request')->request->all());
        $this->flush(false);

        return new Response('ok');
    }

    public function showAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        if ($player->getIsAi()) {
            throw new NotFoundHttpException('Can not show AI player');
        }
        $game = $player->getGame();
        $this->get('lichess.memory')->setAlive($player);

        if(!$game->getIsStarted()) {
            if ($this->get('lichess.memory')->getActivity($player->getOpponent()) > 0) {
                $this->get('lichess.joiner')->join($player);
                $this->flush();
            } else {
                return $this->render('LichessBundle:Player:waitOpponent.html.twig', array('player' => $player));
            }
        }
        $analyser = $this->get('lichess.analyser_factory')->create($game->getBoard());
        $checkSquareKey = $analyser->getCheckSquareKey($game->getTurnPlayer());

        return $this->render('LichessBundle:Player:show.html.twig', array(
            'player'              => $player,
            'opponentActivity'    => $this->get('lichess.memory')->getActivity($player->getOpponent()),
            'checkSquareKey'      => $checkSquareKey,
            'possibleMoves'       => ($player->isMyTurn() && $game->getIsPlayable()) ? $analyser->getPlayerPossibleMoves($player, (bool) $checkSquareKey) : null
        ));
    }

    /**
     * Add a message to the chat room
     */
    public function sayAction($id)
    {
        $message = trim($this->get('request')->get('message'));
        $player = $this->get('lichess.provider')->findPlayer($id);
        $this->get('lichess.messenger')->addPlayerMessage($player, $message);
        $this->flush(false);

        return new Response('ok');
    }

    public function waitAnybodyAction($id)
    {
        try {
            $player = $this->get('lichess.provider')->findPlayer($id);
        } catch(NotFoundHttpException $e) {
            return new RedirectResponse($this->generateUrl('lichess_invite_anybody'));
        }
        if($player->getGame()->getIsStarted()) {
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $this->get('lichess.memory')->setAlive($player);

        $config = new AnybodyGameConfig();
        $config->fromArray($player->getGame()->getConfigArray());
        return $this->render('LichessBundle:Player:waitAnybody.html.twig', array(
            'player' => $player,
            'config' => $config
        ));
    }

    public function cancelAnybodyAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        $game   = $player->getGame();
        if($game->getIsStarted()) {
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $this->get('lichess.starter.anybody')->cancel($player);
        $this->flush();

        return new RedirectResponse($this->generateUrl('lichess_homepage'));
    }

    public function waitFriendAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        if($player->getGame()->getIsStarted()) {
            return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
        }
        $this->get('lichess.memory')->setAlive($player);

        $config = new FriendGameConfig();
        $config->fromArray($this->get('session')->get('lichess.game_config.friend', array()));
        return $this->render('LichessBundle:Player:waitFriend.html.twig', array(
            'player' => $player,
            'config' => $config
        ));
    }

    public function resignAction($id)
    {
        $player = $this->get('lichess.provider')->findPlayer($id);
        try {
            $this->get('lichess.finisher')->resign($this->get('lichess.provider')->findPlayer($id));
            $this->flush();
        } catch (FinisherException $e) {}

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function abortAction($id)
    {
        try {
            $this->get('lichess.finisher')->abort($this->get('lichess.provider')->findPlayer($id));
            $this->flush();
        } catch (FinisherException $e) {}

        return new RedirectResponse($this->generateUrl('lichess_player', array('id' => $id)));
    }

    public function tableAction($id, $color, $playerFullId)
    {
        if($playerFullId) {
            $player = $this->get('lichess.provider')->findPlayer($playerFullId);
            $template = $player->getGame()->getIsPlayable() ? 'table' : 'tableEnd';
        }
        else {
            $player = $this->get('lichess.provider')->findPublicPlayer($id, $color);
            $template = 'watchTable';
        }
        return $this->render('LichessBundle:Game:'.$template.'.html.twig', array(
            'player'           => $player,
            'opponentActivity' => $this->get('lichess.memory')->getActivity($player->getOpponent())
        ));
    }

    public function opponentAction($id, $color, $playerFullId)
    {
        if($playerFullId) {
            $player = $this->get('lichess.provider')->findPlayer($playerFullId);
        } else {
            $player = $this->get('lichess.provider')->findPublicPlayer($id, $color);
        }
        $opponent = $player->getOpponent();

        return $this->opponentPlayerAction($opponent, $playerFullId);
    }

    public function opponentPlayerAction(Player $opponent, $playerFullId)
    {
        if($playerFullId) {
            $template = 'opponent';
        } else {
            $template = 'watchOpponent';
        }
        $opponentActivity = $playerFullId ? $this->get('lichess.memory')->getActivity($opponent) : 2;

        return $this->render('LichessBundle:Player:'.$template.'.html.twig', array(
            'opponent'         => $opponent,
            'opponentActivity' => $opponentActivity,
            'game'             => $opponent->getGame(),
            'playerFullId'     => $playerFullId
        ));
    }

    protected function renderJson($data)
    {
        return new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
    }

    protected function flush($safe = true)
    {
        return $this->get('lichess.object_manager')->flush(array('safe' => $safe));
    }
}
